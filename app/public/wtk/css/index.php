<?php
function wtkGetPost($fncPostVariable, $fncDefault = '') {
    $fncResult = isset($_POST[$fncPostVariable]) ? $_POST[$fncPostVariable] : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;  // $fncResult == ''
    return $fncResult ;
} // end of wtkGetPost

$pgTheme = wtkGetPost('pickTheme','Light');
if ($pgTheme == 'Light'):
	$pgLightChecked = 'checked=""';
	$pgDarkChecked = '';
else:
	$pgLightChecked = '';
	$pgDarkChecked = 'checked=""';
endif;

$pgColorSelect = '';
foreach (glob("wtk*.css") as $pgFile):
    $pgFile = str_replace('wtk', '', $pgFile);
    $pgFile = str_replace('.css', '', $pgFile);
    switch ($pgFile):
        case 'Global':
        case 'Light':
        case 'Dark':
            // skip
            break;
        default:
            $pgColorSelect .= '<option value="' . $pgFile . '">' . $pgFile . '</option>' . "\n";
            break;
    endswitch;
endforeach;
$pgColor = wtkGetPost('pickColor','Blue');
$pgColorSelect = str_replace('>' . $pgColor . '<', ' selected>' . $pgColor . '<', $pgColorSelect);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>CSS Maker by WTK</title>
    <link rel="shortcut icon" href="/imgs/favicon/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
	<link rel="stylesheet" href="wtk<?php echo $pgColor; ?>.css">
	<link id="theme" rel="stylesheet" href="wtk<?php echo $pgTheme; ?>.css">
	<link rel="stylesheet" href="wtkGlobal.css">
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" defer></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js" defer></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.4.6/jscolor.min.js" defer></script>
<style>
.w72 {
    width: 72px !important;
}
</style>
</head>
<body onload="JavaScript:getCssRoot();">
	<div id="myNavbar">
        <ul id="dropdown2" class="dropdown-content">
    <li><a onclick="Javascript:ajaxGo('userList');">Users</a></li>
    <li><a onclick="Javascript:ajaxGo('clientList');">Clients</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('ecomList');">Ecom Providers</a></li>
    <li><a onclick="Javascript:ajaxGo('broadcastList');">Broadcast List</a></li>
    <li><a onclick="Javascript:ajaxGo('reportViewer');">Reports Viewer</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('companyEdit');">Settings</a></li>
  </ul>
  <ul id="dropdown3" class="dropdown-content">
    <li><a onclick="Javascript:ajaxGo('emailTemplates');">Emails</a></li>
    <li><a onclick="Javascript:ajaxGo('affiliateList');">Affiliates</a></li>
    <li><a onclick="Javascript:ajaxGo('prospectList');">Prospects</a></li>
    <li><a onclick="Javascript:ajaxGo('visitorStats');">Visitors</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('revenueList');">Revenue</a></li>
    <li><a onclick="Javascript:ajaxGo('moneyStats');">Money Stats</a></li>
    <li><a onclick="Javascript:ajaxGo('moneyHistory');">History</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('bugList');">Feedback</a></li>
  </ul>
  <ul id="dropdown4" class="dropdown-content">
    <li><a onclick="Javascript:ajaxGo('wtkBuilder');">WTK Builder</a></li>
    <li><a onclick="Javascript:ajaxGo('reportList');">Report Wizard</a></li>
    <li><a onclick="Javascript:ajaxGo('widgetList');">Widgets</a></li>
    <li><a onclick="Javascript:ajaxGo('widgetGroupList');">Widget Groups</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('pageList');">Page List</a></li>
    <li><a onclick="Javascript:ajaxGo('menuSetList');">Menu Sets</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('lookupList');">Lookups</a></li>
    <li><a onclick="Javascript:ajaxGo('helpList');">Help</a></li>
    <li><a onclick="Javascript:ajaxGo('languageList');">Language</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('pickDataTable');">CSV Importer</a></li>
  </ul>
  <ul id="dropdown5" class="dropdown-content">
    <li><a onclick="Javascript:ajaxGo('loginLogList');">Login Log</a></li>
    <li><a onclick="Javascript:ajaxGo('userHistory');">User History</a></li>
    <li><a onclick="Javascript:ajaxGo('updateLogList');">Update Logs</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('errorLogList');">Error Logs</a></li>
    <li><a onclick="Javascript:ajaxGo('failedAttemptList');">Access Fails</a></li>
    <li class="divider"></li>
    <li><a onclick="Javascript:ajaxGo('emailHistory');">Emails Sent</a></li>
  </ul>
        <div class="navbar-fixed">
            <nav class="navbar navbar-home">
                <div class="nav-wrapper">
                    <a data-target="phoneSideBar" class="sidenav-trigger right"><i class="material-icons">menu</i></a>
                    <ul class="right hide-on-med-and-down">
                        <li><a onclick="Javascript:ajaxGo('Dashboard');">Dashboard</a></li>
                        <li><a class="dropdown-trigger" data-target="dropdown2">Client Control<i class="material-icons top-down">arrow_drop_down</i></a></li>
                        <li><a class="dropdown-trigger" data-target="dropdown3">Marketing<i class="material-icons top-down">arrow_drop_down</i></a></li>
                        <li><a class="dropdown-trigger" data-target="dropdown4">Site Management<i class="material-icons top-down">arrow_drop_down</i></a></li>
                        <li><a class="dropdown-trigger" data-target="dropdown5">View Logs<i class="material-icons top-down">arrow_drop_down</i></a></li>
                        <li><a href="/">Logout</a></li>
                    </ul>
                </div>
            </nav>
        </div>
		<!-- Next is Side Menu for Phones -->
        <div class="sidebar-panel">
            <ul id="phoneSideBar" class="sidenav side-nav">
                <li class="no-padding">
                    <ul class="collapsible">
                        <li>
                            <a class="collapsible-header"><i class="material-icons sideDown">arrow_drop_down</i>Client Control</a>
                            <div class="collapsible-body">
                                <ul>
                        <li><a onclick="Javascript:ajaxGo('userList');">Users</a></li>
                        <li><a onclick="Javascript:ajaxGo('clientList');">Clients</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('ecomList');">Ecom Providers</a></li>
                        <li><a onclick="Javascript:ajaxGo('broadcastList');">Broadcast List</a></li>
                        <li><a onclick="Javascript:ajaxGo('reportViewer');">Reports Viewer</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('companyEdit');">Settings</a></li>
                          </ul>
                        </div>
                      </li>
                    </ul>
                    <ul class="collapsible">
                      <li>
                        <a class="collapsible-header"><i class="material-icons sideDown">arrow_drop_down</i>Marketing</a>
                        <div class="collapsible-body">
                          <ul>
                        <li><a onclick="Javascript:ajaxGo('emailTemplates');">Emails</a></li>
                        <li><a onclick="Javascript:ajaxGo('affiliateList');">Affiliates</a></li>
                        <li><a onclick="Javascript:ajaxGo('prospectList');">Prospects</a></li>
                        <li><a onclick="Javascript:ajaxGo('visitorStats');">Visitors</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('revenueList');">Revenue</a></li>
                        <li><a onclick="Javascript:ajaxGo('moneyStats');">Money Stats</a></li>
                        <li><a onclick="Javascript:ajaxGo('moneyHistory');">History</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('bugList');">Feedback</a></li>
                          </ul>
                        </div>
                      </li>
                    </ul>
                    <ul class="collapsible">
                      <li>
                        <a class="collapsible-header"><i class="material-icons sideDown">arrow_drop_down</i>Site Management</a>
                        <div class="collapsible-body">
                          <ul>
                        <li><a onclick="Javascript:ajaxGo('wtkBuilder');">WTK Builder</a></li>
                        <li><a onclick="Javascript:ajaxGo('reportList');">Report Wizard</a></li>
                        <li><a onclick="Javascript:ajaxGo('widgetList');">Widgets</a></li>
                        <li><a onclick="Javascript:ajaxGo('widgetGroupList');">Widget Groups</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('pageList');">Page List</a></li>
                        <li><a onclick="Javascript:ajaxGo('menuSetList');">Menu Sets</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('lookupList');">Lookups</a></li>
                        <li><a onclick="Javascript:ajaxGo('helpList');">Help</a></li>
                        <li><a onclick="Javascript:ajaxGo('languageList');">Language</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('pickDataTable');">CSV Importer</a></li>
                          </ul>
                        </div>
                      </li>
                    </ul>
                    <ul class="collapsible">
                      <li>
                        <a class="collapsible-header"><i class="material-icons sideDown">arrow_drop_down</i>View Logs</a>
                        <div class="collapsible-body">
                          <ul>
                        <li><a onclick="Javascript:ajaxGo('loginLogList');">Login Log</a></li>
                        <li><a onclick="Javascript:ajaxGo('userHistory');">User History</a></li>
                        <li><a onclick="Javascript:ajaxGo('updateLogList');">Update Logs</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('errorLogList');">Error Logs</a></li>
                        <li><a onclick="Javascript:ajaxGo('failedAttemptList');">Access Fails</a></li>
                        <li class="divider"></li>
                        <li><a onclick="Javascript:ajaxGo('emailHistory');">Emails Sent</a></li>
                          </ul>
                        </div>
                      </li>
                    </ul>
                    </li>
                <li><a href="/">Logout</a></li>
             </ul>
        </div><input type="hidden" id="wtkDropDown" value="Y">

		<div id="mainPage">
			<div class="container">
				<h3>CSS Maker <small><a href="/">by Wizard&rsquo;s Toolkit</a></small></h3><br>
				<h4>Choose Colors <small> <a target="_blank" href="WTKcssMaker.zip">download full set</a></small></h4>
				<p>Set the CSS variables which will affect how all elements on your page look. For help with trending
					color palettes, check out <a target="_blank" href="https://coolors.co/palettes/trending">coolors.co</a>.</p>
				<form id="cssForm" class="card b-shadow" style="padding: 20px 15px 0;" target="_blank" method="post" action="showCSS.php">
					<div class="row">
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--gradient-left" name="--gradient-left" value="">
							<label class="active" for="--gradient-left">Gradient Left</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--gradient-right" name="--gradient-right" value="">
							<label class="active" for="--gradient-right">Gradient Right</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--btn-color" name="--btn-color" value="">
							<label class="active" for="--btn-color">Button Color</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--btn-border-color" name="--btn-border-color" value="">
							<label class="active" for="--btn-border-color">Button Border</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--btn-hover" name="--btn-hover" value="">
							<label class="active" for="--btn-hover">Button Hover</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--href-link" name="--href-link" value="">
							<label class="active" for="--href-link">Href Links</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--active-label" name="--active-label" value="">
							<label class="active" for="--active-label">Active Label</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--light-theme-focus" name="--light-theme-focus" value="">
							<label class="active" for="--light-theme-focus">Light Theme Focus</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--dark-theme-focus" name="--dark-theme-focus" value="">
							<label class="active" for="--dark-theme-focus">Dark Theme Focus</label>
						</div>
						<div class="input-field col m2 s12">
							<input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--bg-second-color" name="--bg-second-color" value="">
							<label class="active" for="--bg-second-color">Second Background</label>
						</div>
						<div class="input-field col m3 s12">
							<button type="button" class="btn btn-save tooltipped modal-trigger" data-target="modalWTK" data-tooltip="Generate CSS File">Generate File</button>
						</div>
					</div>
				</form>
				<br>
				<div class="card bg-second b-shadow">
					<div class="card-content">
						<form id="wtkForm" name="wtkForm" method="post">
							<h5>Select Theme and Color</h5>
							<div class="row">
								<div class="input-field col m6 s12">
									Choose Theme
									<p>
										<label for="pickThemeL">
											<input class="with-gap" onclick="JavaScript:swapStyleSheet(this.value)" type="radio" id="pickThemeL" name="pickTheme" value="Light" <?php echo $pgLightChecked; ?>>
											<span>Light</span>
										</label>
									</p>
									<p>
										<label for="pickThemeD">
											<input class="with-gap" onclick="JavaScript:swapStyleSheet(this.value)" type="radio" id="pickThemeD" name="pickTheme" value="Dark" <?php echo $pgDarkChecked; ?>>
											<span>Dark</span>
										</label>
									</p>
								</div>
								<div class="input-field col m6 s6">
									<select id="pickColor" name="pickColor">
										<?php echo $pgColorSelect; ?>
									</select>
									<label for="pickColor" class="active">Choose Color</label>
								</div>
								<div class="col s12 center">
									<button type="button" class="btn-small black b-shadow waves-effect waves-light">Cancel</button>
									&nbsp;&nbsp;
									<button type="submit" class="btn b-shadow waves-effect waves-light">Change Color</button>
								</div>
							</div>
						</form>
					</div>
				</div>
				<br>
				<!--<hr>-->
                <div class="content b-shadow">
					<form id="notUsedForm">
						<div class="row">
                            <div class="col m6 s12">
								<blockquote>Example blockquote tag where MaterializeCSS shows a bar on left.
									<ul>
										<li><a target="_blank" href="https://wizardstoolkit.com/">Wizard&rsquo;s Toolkit</a> low-code development library</li>
										<li><a target="_blank" href="https://wizbits.me/">WizBits</a> shortened URL service</li>
										<li><a target="_blank" href="https://extragood.info/">Mage Page</a> free landing page service</li>
										<li><a target="_blank" href="https://kwiklink.me/">KwikLink</a> your personal page to share your social media, websites, and contact info with a single URL</li>
									</ul>
								</blockquote>
							</div>
							<div class="input-field col m6 s12">
								<table>
									<tr style="border: unset;">
										<td width="150px">
											<img id="imgPreview" src="/wtk/imgs/noPhotoAvail.png" class="materialboxed" width="150">
										</td>
										<td>
											<label for="wtkUpload" class="fileUpload">
												<input type="file" id="wtkUpload" name="wtkUpload" accept="image/*" style="display: none;">
												<span class="btn-floating"><i class="material-icons">file_upload</i></span>
												User Photo
											</label>
										</td>
									</tr>
								</table>
								<div id="photoProgressDIV" class="progress hide">
									<div id="photoProgress" class="determinate" style="width: 0%"></div>
								</div>
								<div id="uploadStatus"></div>
								<span id="uploadFileSize"></span>
								<span id="uploadProgress"></span>
							</div>
						</div>
						<div class="row">
							<div class="input-field col m6 s12">
								<input type="text" id="someText" name="someText" value="">
								<label class="active" for="someText">Some Text Field</label>
							</div>
							<div class="input-field col m6 s12">
								<input type="email" class="validate" id="someEmail" name="someEmail" value="yourEmail@me.com">
								<label class="active" for="someEmail">Email with Validation</label>
								<span class="helper-text" data-error="invalid email format" data-success="valid email format"></span>
							</div>
						</div>
						<div class="row">
							<div class="input-field col m6 s12">
								<i class="material-icons prefix">phone</i>
								<input type="tel" id="somePhone" name="somePhone" value="">
								<label for="somePhone">Phone</label>
							</div>
							<div class="input-field col m6 s12">
								<select id="someSelect" name="someSelect">
									<option value="Emp">Customer Service</option>
									<option value="Mgr">Manager</option>
									<option value="Tech">Tech Support</option>
								</select>
								<label for="someSelect" class="active">Select Drop List</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col m3 s6">
								<input type="text" class="datepicker" id="someDate" name="someDate" style="width: unset !important;">
								<label class="active" for="someDate">Some Date</label>
							</div>
							<div class="input-field col m3 s6">
								<input type="text" class="timepicker" id="someTime" name="someTime">
								<label class="active" for="someTime">Some Time</label>
							</div>
							<div class="input-field col m6 s12">
								<textarea id="someTextarea" class="materialize-textarea"></textarea>
								<label for="someTextarea" class="active">Textarea Example</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col m4 s12">
								<p>
									<label for="someCanPrint">
										<input type="checkbox" value="Y" id="someCanPrint" name="someCanPrint" checked>
										<span>Checkbox Example</span>
									</label>
								</p>
							</div>
							<div class="input-field col m4 s12">
								Switch Example
								<div class="switch">
									<label>
										Off
										<input type="checkbox" checked="checked">
										<span class="lever"></span>
										On
									</label>
								</div>
							</div>
							<div class="input-field col m4 s12">
								Range Example
								<p class="range-field">
									<input type="range" id="someRange" min="0" max="50" />
								</p>
							</div>
						</div>
					</form>
				</div>
				<br>
			<br>
			<div>
                <h4>Report List
                    <small id="filterReset">
                        <button type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
                    </small>
                </h4>
				<form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
					<div class="input-field">
                        <input type="search" class="filter-width-50" name="wtkFilter" id="wtkFilter" value="" placeholder="enter partial Report Name to search for">
                        <input type="search" class="filter-width-50" name="wtkFilter2" id="wtkFilter2" value="" placeholder="enter partial Report Type to search for">
						<button id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
					</div>
				</form>
			</div>
            <div class="wtk-list card b-shadow">
			<table id="wtk-reports" class="striped">
				<thead>
					<tr>
						<th>Report Name</th>
						<th>Report Type</th>
						<th>
							<div align="center">View Order</div>
						</th>
						<th>
							<div align="center">View</div>
						</th>
						<th class="right"><a class="btn-floating btn btn-primary btn-sm"><i class="material-icons">add</i></a></th>
					</tr>
				</thead>
				<tbody>
					<tr id="DwtkReports1">
						<td>User Contact Information</td>
						<td>Core Info</td>
						<td>
							<div align="center">10</div>
						</td>
						<td>
							<div align="center"><a class="btn-floating"><i class="material-icons">format_list_numbered</i></a></div>
						</td>
						<td class="right" nowrap><a class="btn-floating"><i class="material-icons">edit</i></a>
							<span class="btn btn-sm btn-floating"><i class="material-icons">delete</i></span>
						</td>
					</tr>
					<tr id="DwtkReports2">
						<td>Page Views by User</td>
						<td>Analytics</td>
						<td>
							<div align="center">20</div>
						</td>
						<td>
							<div align="center"><a class="btn-floating"><i class="material-icons">insert_chart</i></a></div>
						</td>
						<td class="right" nowrap><a class="btn-floating"><i class="material-icons">edit</i></a>
							<span class="btn btn-sm btn-floating"><i class="material-icons">delete</i></span>
						</td>
					</tr>
					<tr id="DwtkReports5">
						<td>User History by Date Range</td>
						<td>Core Info</td>
						<td>
							<div align="center">30</div>
						</td>
						<td>
							<div align="center"><a class="btn-floating"><i class="material-icons">format_list_numbered</i></a></div>
						</td>
						<td class="right" nowrap><a class="btn-floating"><i class="material-icons">edit</i></a>
							<span class="btn btn-sm btn-floating"><i class="material-icons">delete</i></span>
						</td>
					</tr>
					<tr id="DwtkReports7">
						<td>Revenue Analytics</td>
						<td>Accounting</td>
						<td>
							<div align="center">40</div>
						</td>
						<td>
							<div align="center"><a class="btn-floating"><i class="material-icons">format_list_numbered</i></a></div>
						</td>
						<td class="right" nowrap><a class="btn-floating"><i class="material-icons">edit</i></a>
							<span class="btn btn-sm btn-floating"><i class="material-icons">delete</i></span>
						</td>
					</tr>
					<tr id="wtkReportsfooter">
						<td class="navFooterAlign" align="left" colspan="5">1 - <span id="wtkReportsMaxOnPage">4</span>
							of <span id="wtkReportsMaxRows">4</span> &nbsp;
						</td>
					</tr>
				</tbody>
			</table>
			</div>
			<br>
			<div class="card b-shadow">
				<div class="card-content">
                    <div class="row">
                        <div class="col s12">
        					<h4>Tabs are a nice UI option</h4><br>
                            <ul class="tabs">
                                <li class="tab col s4"><a href="#tab1" class="active">MaterializeCSS</a></li>
                                <li class="tab col s4"><a href="#tab2">Docker</a></li>
                                <li class="tab col s4"><a href="#tab3">Download</a></li>
                            </ul>
                        </div>
                        <div id="tab1" class="col s12">
                            <br><h3>MaterializeCSS</h3>
                            <p><a target="_blank" href="https://materializecss.com/tabs.html">MaterializeCSS</a> is a modern responsive front-end framework based on Material Design.</p>
                            <p>Wizard&rsquo;s Toolkit generates MaterializeCSS code.  Always remember when
                                using MaterializeCSS with jQuery to always declare jQuery.js <em>before</em> materialize.min.js.</p>
                        </div>
                        <div id="tab2" class="col s12">
                            <br><h3>Docker</h3>
                            <p>Docker makes it easy to jump in and have a perfect environment for working on
                                Wizard&rsquo;s Toolkit. Plus, Docker facilitates scaling through Kubernetes
                                and other methods. Wizard&rsquo;s Toolkit provides what we think is an ideal
                                environment, but since this is low-code, you are welcome to change any aspects you want.</p>
                            <p>WTK has <a target="_blank" href="https://hub.docker.com/r/proglabs/wizards-toolkit">Docker
                                 Container</a> options for MySQL, PostgreSQL and Python.</p>
                        </div>
                        <div id="tab3" class="col s12">
                            <br><h3>Download Wizard&rsquo;s Toolkit
                                <small><br>Download and Start Developing 10x</small>
                            </h3>
                            <p>Wizard&rsquo;s Toolkit is the low-code rapid application development
                                using PHP, SQL and JavaScript.</p>
                            <p>Local development and testing is always free.
                            All the resources developers need to start developing with WTK
                            is located at<br>
<a target="_blank" href="http://wizardstoolkit.com/download.php">http://wizardstoolkit.com/download.php</a></p>
                        </div>
                    </div>
				</div>
			</div>
            <br>
			<div class="card bg-second b-shadow">
				<div class="card-content">
					<p>This free utility was built by <a target="_blank" href="https://programminglabs.com/">Programming Labs</a>
						for making color themes for <a target="_blank" href="https://wizardstoolkit.com/">Wizard&rsquo;s Toolkit</a> websites.
						Feel free to modify it for your own needs.</p>
					<p>In your HTML page call the CSS files in this order:</p>
					<blockquote>
						<ul>
							<li>materialize.min.css</li>
							<li>wtk{<strong>YourColor</strong>}.css</li>
							<li>wtkLight.css or wtkDark.css</li>
							<li>wtkGlobal.css</li>
						</ul>
					</blockquote>
					<p>And remember when using MaterializeCSS with jQuery to always declare jQuery.js <em>before</em> materialize.min.js.</p>
				</div>
			</div>
            <br>
		</div>
	</div>
    </div>
    <div id="modalWTK" class="modal content">
        <div class="modal-content">
            <form id="saveCSSform">
                <div class="input-field">
                    <input type="text" class="form-input" id="fileName" name="fileName" value="">
                    <label class="active" for="fileName">Choose File Name</label>
                    <span class="helper-text">wtk will be prepended and file will be saved in this folder</span>
                </div>
            </form>
        </div>
        <div id="modFooter" class="modal-footer right">
            <button type="button" onclick="JavaScript:showCSSfile();" class="btn-small left b-shadow waves-effect waves-light modal-close">Generate without Save</button>

            <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">Cancel</button>
            &nbsp;&nbsp;
            <button type="button" class="btn-primary btn-small b-shadow waves-effect waves-light modal-close" onclick="JavaScript:saveCSS()">Save</button>
        </div>
    </div>
</body>
<script type="text/javascript">
'use strict';
function getCssRoot(){
  $(document).ready(function() {
      M.AutoInit();
      makeAPicker('--gradient-left');
      makeAPicker('--gradient-right');
      makeAPicker('--btn-color');
      makeAPicker('--btn-border-color');
      makeAPicker('--btn-hover');
      makeAPicker('--href-link');
      makeAPicker('--active-label');
      makeAPicker('--light-theme-focus');
      makeAPicker('--dark-theme-focus');
      makeAPicker('--bg-second-color');
      jscolor.trigger('input change');
      let fncElems = document.querySelectorAll('.tooltipped');
      let fncTmp = M.Tooltip.init(fncElems);
      M.updateTextFields();
      let fncTabElem = document.querySelectorAll('.tabs');
      fncTmp = M.Tabs.init(fncTabElem);
  });
}
function makeAPicker(fncClass){
  let fncColor = getComputedStyle(document.documentElement).getPropertyValue(fncClass);
  $('#' + fncClass).val(fncColor);
  $('#' + fncClass).addClass('jscolor');
  var myPicker = new JSColor('#' + fncClass, {preset:'dark'});
}
function swapStyleSheet(fncTheme) {
  document.getElementById('theme').setAttribute('href', 'wtk' + fncTheme + '.css');
}
function setCssRoot(fncId,fncColor){
  if ((fncId == '--gradient-left') || (fncId == '--gradient-right')){
      var fncLeftColor = document.getElementById('--gradient-left').value;
      var fncRightColor = document.getElementById('--gradient-right').value;
      document.documentElement.style.setProperty('--gradient-color', 'linear-gradient(to right, ' + fncLeftColor + ', ' + fncRightColor + ')');
  } else {
      document.documentElement.style.setProperty(fncId, fncColor);
  }
}
function saveCSS(){
  let fncFormData = $('#cssForm').serialize();
  let fncFileName = $('#fileName').val();
  fncFormData = fncFormData + '&fileName=' + fncFileName;
  $.ajax({
      type: "POST",
      url:  'ajxSaveCSS.php',
      data: (fncFormData),
      success: function(data) {
          let fncJSON = $.parseJSON(data);
          switch (fncJSON.result) {
              case 'fileExists':
                  M.toast({html: 'That file name already exists.  Choose a different name.', classes: 'red rounded'});
                  break;
              case 'writeFailed':
                  M.toast({html: 'Writing file failed - check folder permissions.', classes: 'red rounded'});
                  break;
              case 'ok':
                  M.toast({html: 'Your new CSS file has been created.', classes: 'green rounded'});
                  let fncSelect = document.getElementById('pickColor');
                  let fncOpt = document.createElement('option');
                  fncOpt.value = fncFileName;
                  fncOpt.innerHTML = fncFileName;
                  fncSelect.appendChild(fncOpt);
                  $('select').formSelect();
                  break;
          }
      }
  })
}
function showCSSfile(){
    document.forms.cssForm.submit();
    M.toast({html: 'Your new CSS file has been created.', classes: 'green rounded'});
}
function ajaxGo(fncPage){
    alert('In Wizard Toolkit this would direct user to ' + fncPage + ' page using Single-Page Application methodology.')
}
</script>
</html>
