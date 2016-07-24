<?php

# (C) 2016 Matthias Kuhs, Ireland

namespace App\Http\Controllers\Cspot;

use Illuminate\Http\Request;

use Snap\BibleBooks\BibleBooks;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use App\Models\Item;
use App\Models\File;
use App\Models\FileCategory;

use Auth;



class FileController extends Controller
{




    /**
     * FILES/IMAGES HANDLING
     *
     */
    public function index(Request $request)
    {
        $heading = 'List All Files and Images';

        $querystringArray = $request->input();

        # does session contains a filter value?
        if ($request->has('bycategory')) {
            // get only FILES with this specific file_category id
            $file_category = FileCategory::find($request->input('bycategory'));
            if ($file_category) {
                $heading = 'Show Files of type "'.$file_category->name.'"';
                // get all files of this category
                $files = $file_category->files()->paginate(18)->appends($querystringArray);
            }
        }
        if ($request->has('newest')) {
            $heading = 'Recently added files';
            $files = File::orderBy('id', 'desc')->paginate(18);
        }
        // default: show all files
        if (! isset($files)) {
            $files = File::paginate(18);
        }

        // URL contains ...?item_id=xxx (needed in order to add an existing file to an item)
        $item_id = 0;
        if ($request->has('item_id')) {
            $item_id = $request->item_id;
            $heading = 'Select a file for the Plan Item';
        }

        // get list of file categories
        $file_categories = FileCategory::get();

        // for pagination, always append the original query string
        $files = $files->appends($querystringArray);

        return view('admin.files', [
            'files'           => $files, 
            'item_id'         => $item_id, 
            'heading'         => $heading,
            'file_categories' => $file_categories
        ]);
    }


    /**
     * Update information about an existing file
     */
    public function update(Request $request)
    {
        if (! $request->has('id'))
            return;
        $file = File::find($request->id);
        if ($file) {
            $file->filename = $request->filename;
            $file->file_category_id = $request->file_category_id;
            $file->save();
            return 'done!';
        }
        return 'file not found!';
    }




    /**
     * Add existing file to a plan item
     */
    public function add($item_id, $file_id)
    {
        $item = Item::find($item_id);
        if ($item) {
            $file = File::find($file_id);
            $item->files()->save($file);
            correctFileSequence($item_id);
            return \Redirect::route( 'cspot.items.edit', [$item->plan_id, $item->id] );
        }
        flash('Error! Item with ID "' . $id . '" not found');
        return \Redirect::back();
    }



    /**
     * Remove a file attachment
     *
     * - - RESTful API request - -
     *
     * @param int $id
     *
     */
    public function delete($id)
    {
        // find the single resource
        $file = File::find($id);
        if ($file) {
            // check authentication
            if (! Auth::user()->isAdmin() ) {
                return response()->json(['status' => 401, 'data' => 'Not authorized'], 401);
            }
            // delete the physical file
            $destinationPath = config('files.uploads.webpath');
            unlink(public_path().'/'.$destinationPath.'/'.$file->token);
            // also delete possible thumbnail files
            deleteThumbs(public_path().'/'.$destinationPath, $file->token);
            
            // delete the database record
            $file->delete();
            // return to sender
            return response()->json(['status' => 200, 'data' => $file->token.' deleted.']);
        }
        return response()->json(['status' => 404, 'data' => 'Not found'], 404);
    }






    /**
     * Unlink a file attachment
     *
     * - - RESTful API request - -
     *
     * @param int $id
     *
     */
    public function unlink($item_id, $file_id)
    {
        // find the single resource
        $item = Item::find($item_id);
        if ($item) {
            $file = File::find($file_id);
            if ($file->item_id==$item_id) {
                $file->item_id = 0;
                $file->save();
                correctFileSequence($item_id);
                // return to sender
                return response()->json(['status' => 200, 'data' => 'File unlinked.']);
            }
            return response()->json(['status' => 406, 'data' => 'File with id '.$file_id.' not found being linked to item ('.$file->item_id.')!'], 406);
        }
        return response()->json(['status' => 404, 'data' => 'Item with id '.$item_id.' not found!'], 404);
    }




    /**
     * Change seq_no of a file 
     */
    public function move($item_id, $file_id, $direction)
    {
        $item = Item::find($item_id);
        if ($item) {
            $file = File::find($file_id);
            if ($file) {
                if ($direction=='up') {
                    $file->seq_no = $file->seq_no-1.1;
                }
                if ($direction=='down') {
                    $file->seq_no = $file->seq_no+1.1;
                }
                $file->save();
                // make sure all files atteched to this item have the correct seq no now
                correctFileSequence($item_id);
                return \Redirect::route( 'cspot.items.edit', [$item->plan_id, $item->id] );
            }
            flash('Error! File with ID "' . $file_id . '" not found');
        } else {
            flash('Error! Item with ID "' . $item_id . '" not found');
        }
        return \Redirect::back();
    }


}
