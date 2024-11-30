$pgHtm .=<<<htmVAR
    <h5>@FilterTitle@ <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('@FileName@','@Table@','$gloRNG')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h5>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width">
              <input value="$pgFilterValue" name="wtkFilter" id="wtkFilter" type="search" placeholder="enter partial value to search for">
           </div>
           @filter2@
           <button onclick="Javascript:wtkBrowseFilter('@FileName@','@Table@')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
htmVAR;
