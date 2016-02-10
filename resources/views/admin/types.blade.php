
<!-- # (C) 2016 Matthias Kuhs, Ireland -->

@extends('layouts.main')

@section('title', $heading)

@section('types', 'active')



@section('content')


	@include('layouts.flashing')

	@if(Auth::user()->isEditor())
	<a class="btn btn-primary-outline pull-xs-right" href="{{ url('admin/types/create') }}">
		<i class="fa fa-plus"> </i> &nbsp; Add a new type
	</a>
	@endif

    <h2>
    	{{ $heading }}
    	<small class="text-muted">
    		<a tabindex="0" href="#"
    			data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="focus"
    			data-content="Service Types determine the title of services and which default items can be inserted for new plans.">
    			<i class="fa fa-question-circle"></i></a>
		</small>
	</h2>


	@if (count($types))

		<table class="table table-striped table-bordered 
					@if(count($types)>5)
					 table-sm
					@endif
					 ">
			<thead class="thead-default">
				<tr>
					<th class="center">#</th>
					<th>Name</th>
					<th class="center">Total No. of Plans</th>
					 @if( Auth::user()->id===1 || Auth::user()->isAdmin() )
						<th class="center">Action</th>
					@endif
				</tr>
			</thead>

			<tbody>

	        @foreach( $types as $type )
				<tr>

					<td class="center" scope="row">{{ $type->id }}</td>

					<td class="link" onclick="location.href='{{ url('cspot/plans/by_type/'.$type->id) }}'" 
						title="Show all upcoming Plans of this Type of Service">{{ $type->name }}</td>

					<td class="link center" onclick="location.href='{{ url('cspot/plans/by_type/'.$type->id) }}/all'" 
						title="Show all Plans of this Type of Service">{{ $type->plans->count() }}</td>

					<td class="nowrap center">
						<a class="btn btn-secondary btn-sm" title="Show upcoming Plans" href='{{ url('cspot/plans/by_type/'.$type->id ) }}'><i class="fa fa-filter"></i></a>
						 @if( Auth::user()->isEditor() )
							<a class="btn btn-primary-outline btn-sm" title="Edit" 
								href='{{ url('admin/types/'.$type->id) }}/edit'>
									<i class="fa fa-pencil"></i></a>
							<a class="btn btn-danger btn-sm" title="Delete!" 
								href='{{ url('admin/types/'.$type->id) }}/delete'>
									<i class="fa fa-trash"></i></a>
						@endif
					</td>

				</tr>
	        @endforeach

			</tbody>

		</table>

    @else

    	No types found!

	@endif

	
@stop
