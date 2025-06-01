<?PHP
/**
* Add Custom Client functions here to be included in Wizard's Toolkit environment
*/

// Custom Client functions can go here
/*
Can add here custom report filtering based on Security Levels or Roles
Be careful because these will be used for ALL WTK reports.

if ($gloUserSecLevel == 1):
    $gloUserSQLJoin = '';
    $gloUserSQLWhere = '';
endif;

Can also put in a global search and replace to make sure you do not have cache problems.
For example:
wtkSearchReplace('custom.js','custom4.js');

If you want to globally add wtkPageReadOnlyCheck for all Edit pages you could do it here like:
if (stripos($gloMyPage, 'Edit.php') !== false):
    $gloForceRO = wtkPageReadOnlyCheck($gloMyPage, $gloId);
endif;
*/

/**
* Hard-coded navbar when you do not want to use data-driven menus.
*
* This code is put into ClientFuncs.php because it will likely need to be edited for every website.
*
* To add Notification Bell, add this code just after <a id="hamburger" ...
*/
/*
<span class="counter-icon right" style="margin-right:20px">
    <i class="material-icons small white-text" style="padding: 14px 0px">notifications</i>
    <span id="alertCounter" class="counter-icon-badge">3</span>
</span>
*/

/*
* Example usage:
* <code>
* wtkSearchReplace('<!-- @wtkMenu@ -->', wtkNavBar('Your Company Name'));
* </code>
*
* @param string $fncHeader pass this in for the top-center title to show
* @return html of top navbar and side-menu
*/
function wtkNavBarTailwind($fncHeader){
    $fncHtm =<<<htmVAR
<div class="navbar bg-neutral text-neutral-content">
    <div class="navbar-start">
        <div class="dropdown">
            <div tabindex="0" role="button" class="btn m-1">
                <svg class="wtk-icon"><use href="/imgs/icons.svg#icon-menu"/></svg>
            </div>
            <ul tabindex="0" class="dropdown-content menu min-w-max bg-base-100 p-2 shadow-sm">
                <li><a onclick="Javascript:goHome();">Dashboard</a></li>
                <li><a onclick="Javascript:ajaxGo('user');">My Profile</a></li>
                <li><a onclick="Javascript:ajaxGo('reportViewer');">Reports</a></li>
                <li><a onclick="Javascript:ajaxGo('chatList');">Chat</a></li>
                <li><a onclick="Javascript:ajaxGo('forumList');">Forum</a></li>
                <li><a onclick="Javascript:ajaxGo('messageList');">Message</a></li>
                <li><a onclick="Javascript:showBugReport();">Report Bug</a></li>
                <li><a onclick="Javascript:wtkLogout();">Log Out</a></li>
            </ul>
        </div>
    </div>
    <div class="navbar-center text-xl">$fncHeader</div>
    <div class="navbar-end">
        <a onclick="wtkLogout();" class="btn btn-ghost btn-circle"><svg class="wtk-icon"><use href="/imgs/icons.svg#icon-logout"></use></svg></a>
    </div>
</div>
htmVAR;
    return $fncHtm;
} // wtkNavBarTailwind


/*
* Example usage:
* <code>
* wtkSearchReplace('<!-- @wtkMenu@ -->', wtkNavBar('Your Company Name'));
* </code>
*
* @param string $fncHeader pass this in for the top-center title to show
* @return html of top navbar and side-menu
*/
function wtkNavBar($fncHeader){
    $fncHtm =<<<htmVAR
    <div class="navbar-fixed">
        <div class="navbar navbar-home">
            <div class="row">
                <div class="col s1 m3" style="margin-top:12px">
                    <a id="backBtn" onclick="JavaScript:wtkGoBack()" class="hide"><i class="material-icons small white-text">navigate_before</i></a>
                </div>
                <div class="col s10 m6 center">
                    <h4 style="padding-top:12px">$fncHeader</h4>
                </div>
                <div class="col s1 m3">
                    <a id="hamburger" data-target="phoneSideBar" class="sidenav-trigger show-on-large hide right"><i class="material-icons small white-text">menu</i></a>
                </div>
            </div>
        </div>
    </div>
	<!-- sidebar -->
	<div class="sidebar-panel">
		<ul id="phoneSideBar" class="collapsible sidenav side-nav">
			<li>
				<div class="user-view">
					<div class="background">
						<img src="/imgs/sunset.jpg">
					</div>
					<img class="circle responsive-img" id="myPhoto" src="/wtk/imgs/noPhotoAvail.png">
					<span class="name" id="myName">@FullName@</span>
				</div>
			</li>
			<li><a class="sidenav-close" onclick="Javascript:goHome();"><i class="material-icons">dashboard</i>Dashboard</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('user');"><i class="material-icons">account_box</i>My Profile</a></li>
			<li><a class="sidenav-close" onclick="Javascript:ajaxGo('reportViewer');"><i class="material-icons">insert_chart</i>Reports</a></li>
            <li><a class="sidenav-close" onclick="Javascript:ajaxGo('chatList');"><i class="material-icons">chat</i>Chat</a></li>
			<li><a class="sidenav-close" onclick="Javascript:ajaxGo('forumList');"><i class="material-icons">forum</i>Forum</a></li>
			<li><a class="sidenav-close" onclick="Javascript:ajaxGo('messageList');"><i class="material-icons">message</i>Message</a></li>
			<li><a class="sidenav-close" onclick="Javascript:showBugReport();"><i class="material-icons">bug_report</i>Report Bug</a></li>
			<li><a class="sidenav-close" onclick="Javascript:wtkLogout();"><i class="material-icons">close</i>Log Out</a></li>
		</ul>
	</div>
	<!-- end sidebar -->
htmVAR;
    return $fncHtm;
} // wtkNavBar
?>
