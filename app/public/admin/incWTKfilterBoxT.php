    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="bg-white p-4 rounded-lg shadow-md mb-6">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="flex md:flex-row gap-4 items-end">
            <div class="flex-auto">
                <label for="wtkFilter" class="block text-sm font-medium text-gray-700 mb-1">@LabelOne@</label>
                <input value="$pgFilterValue" name="wtkFilter" id="wtkFilter" type="search" placeholder="enter partial value to search for" class="input">
            </div>
            @filter2@
            <div class="flex-none">
                <button onclick="Javascript:wtkBrowseFilter('@FileName@','@Table@')" id="wtkFilterBtn" type="button" class="btn btn-secondary">
                    <svg class="wtk-icon"><use href="/imgs/icons.svg#icon-search"/></svg>
                    <span>Search</span>
                </button>
            </div>
        </div>
    </form>
