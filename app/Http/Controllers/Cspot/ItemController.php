<?php

# (C) 2016 Matthias Kuhs, Ireland

namespace App\Http\Controllers\Cspot;

use Illuminate\Http\Request;

use Snap\BibleBooks\BibleBooks;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use App\Models\Song;
use App\Models\Plan;
use App\Models\Item;
use App\Models\File;
use App\Models\FileCategory;

use DB;
use Auth;
use Log;



class ItemController extends Controller
{



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $plan_id, $seq_no)
    {
        // get the plan to which we want to add an item
        $plan = Plan::find( $plan_id );

        // check user rights (teachers and leaders can edit items of their own plan)
        if (! checkRights($plan)) {
            return redirect()->back();
        }


        $beforeItem = null;
        // check if this is an insertion of an item BEFORE another item
        if ($request->is('*/before/*')) {
            // here, $seq_no actually is the id of the item before which we want to insert a new item
            $beforeItem = Item::find( $seq_no );
            // Make sure we always insert the new item right BEOFRE the current item
            $seq_no = ($beforeItem->seq_no) - 0.1;
            Log::info( 'CREATE-Showing form to create new item to be inserted before '.$beforeItem->seq_no.' '.$beforeItem->id.' - '.$beforeItem->comment );
        }

        // show the form
        return view( 'cspot.item', [
                'plan'         => $plan, 
                'beforeItem'   => $beforeItem, 
                'seq_no'       => $seq_no,
                'versionsEnum' => json_decode(env('BIBLE_VERSIONS')),   // array of possible bible versions
                'bibleBooks'   => new BibleBooks(),                     // array of bible books
                'bibleTexts'   => [],
            ]);
    }





    /**
     * Store a newly created ITEM in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $plan = Plan::find( $request->input('plan_id') );
        // searching for a song?
        if ($request->has('search')) {
            $songs = songSearch( '%'.$request->search.'%' );
            if (!count($songs)) {
                flash('No songs found for '.$request->search);
                return redirect()->back()->withInput();
            }
            // Success! Just one song found! Update the request with the new song_id
            if (count($songs)==1) {
                $request->song_id = $songs[0]->id;
            } else {
                // as we found several songs, return to view as user must select one
                Log::info('STORE-search resulted in several songs - SeqNo:'.$request->seq_no.' BeforeITemId:'.$request->beforeItem_id);
                return view('cspot.item_select_song', [
                    'songs'      => $songs,
                    'plan'       => $plan,
                    'item_id'    => 0,
                    'seq_no'     => $request->seq_no,
                    'moreItems'  => $request->moreItems,
                    'beforeItem_id' => $request->beforeItem_id,
                ]);
            }
        }

        // check user rights (teachers and leaders can edit items of their own plan)
        if (! checkRights($plan)) {
            return redirect()->back();
        }

        // check if the new item contains at least one of the following:
        //       Song, Bible reference or Comment
        if ($request->comment=='' && $request->version=='' && (!isset($request->song_id) || (isset($request->song_id)&&$request->song_id==0)) ) {
            flash('item was empty! Please add a comment, select a bible verse or add a song.');
            return redirect()->back();
        }        

        $beforeItem = null;
        if (isset($request->beforeItem_id)) {
            $befItem = Item::find($request->beforeItem_id);
            if ($befItem) {
                $beforeItem = $befItem;
                $request->seq_no = ($beforeItem->seq_no) - 0.1;
            }
        }

        // review numbering of current items for this plan and insert the new item
        $plan = insertItem( $request );

        $xfi = isset($beforeItem->id)  ?  $beforeItem->id.' seqNo:'.$beforeItem->seq_no  :  'missing!';
        Log::info("STORE - Inserting new item (id=$plan->newest_item_id) into plan with seq.No ".$request->seq_no.' - befItemId? '.$xfi);

        // see if user ticked the checkbox to add another item after this one
        if ($request->moreItems == "Y") {

            // get array of possible bible versions
            $versionsEnum = json_decode(env('BIBLE_VERSIONS'));
            // get array of bible books
            $bibleBooks = new BibleBooks();

            // return back to same view
            return view( 'cspot.item', [
                    'plan'         => $plan, 
                    'seq_no'       => $request->seq_no, 
                    'versionsEnum' => $versionsEnum,
                    'moreItems'    => 'Y',
                    'beforeItem'   => $beforeItem,
                    'bibleBooks'   => $bibleBooks,
                    'bibleTexts'   => [],
                ]);
        }

        // all went well and the user sees the result anyway, so no flash message needed:
        unFlash();

        // provide new item id to the view for highlighting
        session()->put('newest_item_id', $plan->newest_item_id);

        // back to full plan view
        //      but first, get plan id from the hidden input field in the form
        return \Redirect::route( 'cspot.plans.edit', [ 'id' => $request->input('plan_id') ]);
    }







    /**
     * Add a song as a new item into a plan (at the end)
     * 
     * this is called from the Songs List View
     */
    public function addSong($plan_id, $song_id )
    {
        // check user rights (teachers and leaders can edit items of their own plan)
        $plan = Plan::find( $plan_id );
        if (! checkRights($plan)) {
            return redirect()->back();
        }

        if ($plan) {
            // get all current items of this plan
            $items = $plan->items;

            // find item with the highest seq.no
            $seq_no = 50; // (temporary solution)               

            // create a new items object add it to this plan
            $item = new Item([
                'seq_no' => $seq_no, 
                'song_id' => $song_id,
            ]);
            $newItem = $plan->items()->save($item);

            // provide new item id to the view for highlighting
            session()->put('newest_item_id', $newItem->id);

            return \Redirect::route( 'cspot.plans.edit', $plan_id );
        }

        flash('Error! Plan with ID "' . $plan_id . '" not found! (F:addSong)');
        return \Redirect::route('home');        
    }



    /**
     * Directly insert a song as a new item into a plan
     */
    public function insertSong($plan_id, $seq_no, $song_id, $moreItems=null, $beforeItem_id=null )
    {
        // check user rights (teachers and leaders can edit items of their own plan)
        $plan = Plan::find( $plan_id );
        if (! checkRights($plan)) {
            return redirect()->back();
        }

        $beforeItem = [];
        // find the seq_no ot the item before which I want to insert this new item
        if ( isset($beforeItem_id) ) {
            $beforeItem = Item::find($beforeItem_id);
            $seq_no = ($beforeItem->seq_no) - 0.1;
        }

        // create a new items object add it to this plan
        $item = new Item([
            'seq_no' => $seq_no, 
            'song_id' => $song_id,
        ]);
        $newItem = $plan->items()->save($item);

        // re-number all items 
        $item = moveItem($newItem->id, 'static');

        Log::info('INSERSONG-'.$seq_no.' new Id:'.$newItem->id.' new seqNo:'.$newItem->seq_no.' befItemId:'.$beforeItem_id);

        if ($moreItems=='Y') {

            $versionsEnum = json_decode(env('BIBLE_VERSIONS'));

            // insert another item after the just created item
            $seq_no = $newItem->seq_no + 1;

            // send confirmation to view
            flash('New item No '.$newItem->seq_no.' inserted with song '.$newItem->song->title);

            $bibleBooks = new BibleBooks();
            
            // show the form
            return view( 'cspot.item', [
                    'plan'         => $plan, 
                    'seq_no'       => $seq_no, 
                    'versionsEnum' => $versionsEnum,
                    'beforeItem'   => $beforeItem,
                    'bibleBooks'   => $bibleBooks,
                    'bibleTexts'   => [],
                ]);
        }

        // provide new item id to the view for highlighting
        session()->put('newest_item_id', $newItem->id);

        return \Redirect::route( 'cspot.plans.edit', $plan_id );
    }






    /**
     * Display single items of a plan with options to move to the next or previous item on this plan
     *
     * @param  int     $id      item id
     * @param  string  $present (optional) chords (default), sheetmusic or present (for overhead presentations)
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id, $present=null)
    {
        $item = Item::find($id);

        if ($item) {
            // default is to show chords
            if ( ! $present) { $present = 'chords'; }

            return view('cspot.'.$present, [
                    'item'       => $item,          
                    // all items of the plan to which this item belongs
                    'items'      => $item->plan->items->sortBy('seq_no')->all(),         
                    // what kind of item presentation is requested
                    'type'       => $present,       
                    // the bible text if there was any reference in the comment field of the item
                    'bibleTexts' => getBibleTexts($item->comment)
                ]);
        }

        flash('Error! Item with ID "' . $id . '" was not found! (F:show)');
        return \Redirect::route('home');        
    }






    /**
     * show the previous or NEXT item in the list of items related to a plan
     *  or swap between chords and sheetmusic 
     *
     * @param  int     $id          plan id
     * @param  int     $id          item id  or: 'seq-no-<seq_no>' !
     * @param  string  $direction   next|previous|swap
     * @param  string  $chords      (chords|sheetmusic)
     *
     * @return \Illuminate\Http\Response
     */
    public function next(Request $request, $plan_id, $item_id, $direction, $chords=null)
    {
        // get item id of next or previous item from helper function
        $new_item_id = nextItem( $plan_id, $item_id, $direction );

        // call edit with new item id 
        if ($chords==null) {
            return $this->edit( $plan_id, $new_item_id );
        } 

        // swap between showing chords or sheetmusic
        if ($direction=='swap') {
            if ($chords=='chords') 
                $chords = 'sheetmusic';
            else
                $chords = 'chords';
        }
        if ($chords=='chords') {
            return $this->show( $new_item_id );
        }
        return $this->show( $new_item_id, $chords );
    }





    /**
     * Show the form for editing an existing ITEM.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($plan_id, $id)
    {
        // get current item
        $plan = Plan::find( $plan_id );

        $item = Item::find($id);
        if (!$item) { 
            flash('Error! Item with ID "' . $id . '" not found! (F:edit)');
            return redirect()->back();
        }
        $seq_no = $item->seq_no;

        $versionsEnum = json_decode(env('BIBLE_VERSIONS'));

        // If this is a song, find out the last time it was used, 
        // that is: Find the newest plan containing an item with this song
        $newestUsage = [];
        $usageCount = 0;
        if ($item->song) {
            $plans       = $item->song->plansUsingThisSong(); // get all plans using this song
            $usageCount  = count($plans);
            $newestUsage = $plans->first();
        } 

        // check if comment contains a bible reference, then get the bible text
        $bibleTexts = getBibleTexts($item->comment);

        // array of books of the bible
        $bibleBooks = new BibleBooks();
        
        // get list of items for this plan, each with a prober 'title'
        $items = $item->plan->items->sortBy('seq_no')->all();

        $songs = []; # send empty song array
        // send the form
        return view( 'cspot.item', [
                'plan'         => $plan, 
                'seq_no'       => $seq_no, 
                'item'         => $item, 
                'items'        => $items, 
                'songs'        => $songs, 
                'versionsEnum' => $versionsEnum,
                'usageCount'   => $usageCount,
                'newestUsage'  => $newestUsage,
                'bibleBooks'   => $bibleBooks,
                'bibleTexts'   => $bibleTexts,
            ]);
    }



    /**
     * Update the specified ITEM in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $seq_no=null)
    {
        $item    = Item::find($id);
        if (!$item) {
            flash('Error! Item with ID "' . $id . '" not found! (F:update)');
            return \Redirect::back();
        }
        $plan_id = $item->plan_id;
        $plan    = Plan::find( $plan_id );

        // searching for a song?
        // (but not when changing the seq.no)
        if ( $request->has('search') && $seq_no == null ) {
            $songs = songSearch( '%'.$request->search.'%' );
            if (!count($songs)) {
                flash('No songs found for '.$request->search);
                return redirect()->back()->withInput();
            }
            if (count($songs)==1) {
                $request->song_id = $songs[0]->id;
            } else {
                // as we found several songs, user must select one
                return view('cspot.item_select_song', [
                    'songs'     => $songs, 
                    'plan'      => $plan, 
                    'item_id'   => $id,
                    'seq_no'    => $request->seq_no,
                    'moreItems' => 'N',
                ]);
            }
        }

        // check user rights (teachers and leaders can edit items of their own plan)
        if (! checkRights($plan)) {
            return redirect()->back();
        }

        // handle file uplaods
        if ($request->hasFile('file')) {
            if ($request->file('file')->isValid()) {
                // use the helper function
                $file = saveUploadedFile($request);
                // add the file as a relationship to the song
                $item->files()->save($file);
                // correct seq_no of attached files (if more than one)
                correctFileSequence($id);
            }
            else {
                flash('Uploaded file could not be validated!');
            }
        }

        if ($seq_no==null) {
            // get all item fields from the request
            $fields = $request->except('_token', '_method');
            // if a single song was selected above, add it to the array
            if (isset($songs)) {
                $fields['song_id'] = $songs[0]->id;
            }
            $item->update( $fields );
        } else {
            // only update the sequence number
            $item->seq_no = $seq_no;
            $item->save();
            $newItem = moveItem( $item->id, 'static');
        }

        // back to full plan view 
        return \Redirect::route('cspot.plans.edit', $plan_id);
    }



    /**
     * Directly update an item with a new song
     */
    public function updateSong($plan_id, $item_id, $song_id )
    {
        // check user rights (teachers and leaders can edit items of their own plan)
        $plan = Plan::find( $plan_id );
        if (! checkRights($plan)) {
            return redirect()->back();
        }

        $item = Item::find($item_id);
        $item->update([
            'song_id' => $song_id,
        ]);

        // provide new item id to the view for highlighting
        session()->put('newest_item_id', $item->id);

        // send confirmation to view
        flash('New item No '.$item->seq_no.' inserted with song '.$item->song->title);

        return \Redirect::route( 'cspot.plans.edit', $plan_id );
    }




    /**
     * API - update single fields of item via AJAX
     */
    public function APIupdate(Request $request, $item_id=null)
    {
        // is this a generic item update (different field names)
        if ($request->has('id') && $request->has('value') ) {
            $field_name = explode('-', $request->id)[0];
            $item_id    = explode('-', $request->id)[3];
        }
        // or is this a specific update of the song id?
        elseif ($item_id) {
            $field_name = 'song_id';
            $request->value = $request->song_id;
        }
        else { 
            return response()->json(['status' => 404, 'data' => 'APIupdate: item_id missing!'], 404);
        }
        // find the single resource
        $item = Item::find($item_id);
        if ($item) {
            // check authentication
            $plan = Plan::find( $item->plan_id );
            if (! checkRights($plan)) {
                return response()->json(['status' => 401, 'data' => 'Not authorized'], 401);
            }
            $item->update( [$field_name => $request->value] );
            // return text to sender
            return $item[$field_name];
        }
        return response()->json(['status' => 404, 'data' => 'APIupdate: item not found'], 404);
    }



    /**
     * API - delete item via AJAX
     */
    public function APIdelete($id)
    {
        // find the single resource
        $item = Item::find($id);
        if ($item) {
            // check authentication
            $plan = Plan::find( $item->plan_id );
            if (! checkRights($plan)) {
                return response()->json(['status' => 401, 'data' => 'Not authorized'], 401);
            }
            // get item and delete it
            $item = deleteItem($id);
            if ($item) {
                return response()->json(['status' => 200, 'data' => 'Item deleted.']);
            }
            return response()->json(['status' => 405, 'data' => 'Item not deleted!']);
        }
        return response()->json(['status' => 404, 'data' => 'Not found'], 404);
    }




    /**
     * Unlink a song attachment
     *
     * - - RESTful API request - -
     *
     * @param int $item_id
     * @param int $song_id
     *
     */
    public function unlinkSong($item_id, $song_id)
    {
        // find the single resource
        $item = Item::find($item_id);
        if ($item) {
            if ($item->song_id==$song_id) {
                $item->song_id = 0;
                $item->save();
                // return to sender
                return response()->json(['status' => 200, 'data' => 'Song unlinked.']);
            }
            return response()->json(['status' => 406, 'data' => 'Song with id '.$song_id.' not found being linked to item ('.$item->song_id.')!'], 406);
        }
        return response()->json(['status' => 404, 'data' => 'Item with id '.$item_id.' not found!'], 404);
    }





    /**
     * MOVE the specified resource up or down in the list of items related to a plan.
     *
     * @param  int     $id
     * @param  string  $direction
     * @return \Illuminate\Http\Response
     */
    public function move($id, $direction)
    {
        // call helper function to do the actual 'move'
        $item = moveItem($id, $direction);
        if ($item) {
            // back to full plan view 
            return \Redirect::back();
        }
        flash('Error! Item with ID "' . $id . '" not found');
        return \Redirect::back();
    }


    /**
     * REMOVE the specified resource from storage.
     *(if the model allows soft-deletes)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function trash($id)
    {
        $item    = Item::find($id);
        if ($item) {
            $plan_id = $item->plan_id;
            $plan    = Plan::find( $plan_id );
        } 
        else {
            flash('Error! Item with ID "' . $id . '" not found! (F:trash)' );
            return \Redirect::back();
        }

        // check user rights (teachers and leaders can edit items of their own plan)
        if (! checkRights($plan)) {
            return redirect()->back();
        }

        // get item and delete it
        $item = deleteItem($id);
        if ($item) {
            // back to full plan view 
            //flash('Item deleted.');
            return \Redirect::route('cspot.plans.edit', $plan_id);
        }
        flash('Error! Problem trying to delete item with ID "' . $id . '"');
        return \Redirect::back();
    }




    /**
     * Restore the item (previously soft-deleted)
     *(if the model allows soft-deletes)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        // check user rights (teachers and leaders can edit items of their own plan)
        $item    = Item::onlyTrashed()->find($id);
        $plan_id = $item->plan_id;
        $plan    = Plan::find( $plan_id );
        if (! checkRights($plan)) {
            return redirect()->back();
        }

        // get item and restore it
        $item = restoreItem($id);
        if ($item) {
            // mark restored item as 'newest item'
            session()->put(['newest_item_id' => $id]);
            // back to full plan view 
            return \Redirect::back();
        }
        flash('Error! Item with ID "' . $id . '" not found! (F:restore)');
        return \Redirect::back();
    }



    /**
     * PERMANENTLY DELETE an item 
     *
     */
    public function permDelete( $id )
    {
        $item    = Item::onlyTrashed()->find($id);
        if (!$item) {
            flash('Error! Item with ID "' . $id . '" not found! (F:permDelete)');
            return redirect()->back();
        }

        // check if user is leader of the corresponding plan or author/admin
        if ( $item->plan->leader_id==Auth::user()->id || Auth::user()->isAuthor() ) {

            $item->forceDelete();

            flash('Trashed item with id '.$id.' deleted permanently');
            return \Redirect::back();
        }
        flash('Sorry, only plan leader or Author can delete items');
        return redirect()->back();
    }


    /**
     * PERMANENTLY DELETE all trashed items of a plan
     *
     */
    public function deleteAllTrashed( $plan_id )
    {
        $plan = Plan::find($plan_id);
        // check if user is leader of the corresponding plan or author/admin
        if ( ! $plan->leader_id==Auth::user()->id || ! Auth::user()->isAuthor() ) {
            flash('Sorry, only plan leader or Author can delete items');
            return redirect()->back();
        }
        // this item should be restored
        $items = Item::onlyTrashed()->where('plan_id', $plan_id);
        if (!$items) return false;

        $items->forceDelete();

        flash('All trashed items deleted');
        return \Redirect::back();
    }


    /**
     * RESTORE all trashed items of a plan
     *
     */
    public function restoreAllTrashed( $plan_id )
    {
        // this item should be restored
        $items = Item::onlyTrashed()->where('plan_id', $plan_id)->get();
        if (!$items) return false;

        // restore all items and try to restore their correct sequence number
        foreach ($items as $item) {
            restoreItem($item->id);
        }

        flash('All trashed items restored. Please review the sequence!');
        return \Redirect::back();
    }




}

