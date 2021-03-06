
<th class="{{ $thclass . (Request::has('orderby') && Request::get('orderby')==$thfname ? ' text-info' : '') }}">

    <span class="link" onclick="reloadListOrderBy('{{ $thfname }}')" 
        data-toggle="tooltip" title="Sort list by {{ ucfirst($thdisp) }}">
            {{ ucfirst($thdisp) }}
            <i class="fa fa-sort"> </i>
    </span>


    @if ( $thsearch )
        <span id="{{ $thfname }}-search" class="link" onclick="showFilterField('{{ $thfname }}')" data-toggle="tooltip" 
            @if ( Request::has('filterby') && Request::get('filterby')==$thfname )
                    title="Clear filter">
                Filter: <span class="bg-info">&nbsp;{{ Request::get('filtervalue') }} </span>
                <i id="filter-{{ $thfname }}-clear" class="fa fa-close"> </i>
            @else
                    title="Search within {{ $thdisp }} (Use '_' to search for empty fields!)">
                <i id="filter-{{ $thfname }}-show" class="fa fa-search"> </i>
            @endif
        </span>
    @endif

</th>
