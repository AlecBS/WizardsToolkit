<?php
/**
* This contains Wizard's Toolkit functions involving social media.
*
* These functions have not been tested since 2013.  Need to review and verify.
*
* All rights reserved.
*
* This file is only usable by subscribers of the Wizard's Toolkit.  It may also
* be used while testing on localhost but not deployed to a production server until
* subscription is active.  You may not, except with our express written permission,
* distribute or commercially exploit the content.  Nor may you transmit it or store
* it in any other website or other form of electronic retrieval system.
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
* OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2025, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

$pgAddThisJS = false;

/**
 * Add the 'AddThis' SmartLayers to your website
 *
 * Adds necessary Javascript to page just before </head>
 *
 * @param  string $fncAddThisID  is the AddThis unique identifier you get when signing up with their service
 * @param  string $fncFaceBookID the Facebook page link
 * @param  string $fncTwitterID  the Twitter account ID
 *
 * @return null   nothing returned because automatically adds JS to page before </head>
 */
function wtkSocialSmartLayer($fncAddThisID, $fncFaceBookID, $fncTwitterID) {
// Go to http://www.addthis.com/get/smart-layers to customize
    global $pgAddThisJS;
    if ($pgAddThisJS == false):
        $pgAddThisJS = true;
        wtkSearchReplace('</head>', '<script type="text/javascript" src="https://s7.addthis.com/js/300/addthis_widget.js#pubid=' . $fncAddThisID . '"></script>' . "\n" . '</head>');
    endif;  // $pgAddThisJS == false

    $fncResult =<<<FNCVAR
<script type="text/javascript">
  addthis.layers({
    'theme' : 'transparent',
    'share' : {
      'position' : 'left',
      'numPreferredServices' : 6,
      'services' : 'facebook,twitter,livejournal,blogger,email,more'
    },
    'follow' : {
      'services' : [
        {'service': 'facebook', 'id': '$fncFaceBookID'},
        {'service': 'twitter', 'id': '$fncTwitterID'}
      ]
    }
  });
</script>
FNCVAR;

    wtkSearchReplace('</head>', $fncResult . "\n" . '</head>');
}  // end of wtkSocialSmartLayer

/**
 * Add a Twitter Feed to the website
 *
 * Adds necessary Javascript to page just before </head>
 *
 * @param  string $fncTwitterAcct  the Twitter account ID
 * @param  string $fncWidgetID     this has to be gotten from Twitter for this particular account
 * @param  int    $fncWidth        optional way to pass width
 * @param  int    $fncHeight       optional way to pass Height
 *
 * @return string the HTML that will contain the Twitter feed
 */
function wtkHtmShowTwitter($fncTwitterAcct, $fncWidgetID, $fncWidth = '', $fncHeight = '') {
/* -----------------JMN 6/7/2012 12:22PM-----------------
    Displays Twitter widget.  This has a specific height and color, you might want to go to Twitter and
    get your own widget and put the code here.
    https://dev.twitter.com/docs/embedded-timelines#customization
 --------------------------------------------------*/
    $fncJS =<<<FNCVAR
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
FNCVAR;

    wtkSearchReplace('</head>', $fncJS . "\n" . '</head>');

    $fncTwitter  = '<a class="twitter-timeline" href="https://twitter.com/' . $fncTwitterAcct . '" data-widget-id="' . $fncWidgetID . '"';
    if ($fncWidth != ''):
        $fncTwitter .= ' width="' . $fncWidth . '"';
    endif;  // $fncWidth != ''
    if ($fncHeight != ''):
        $fncTwitter .= ' height="' . $fncHeight . '"';
    endif;  // $fncHeight != ''
   $fncTwitter .= '>Tweets by @' . $fncTwitterAcct . '</a>' . "\n";
    return $fncTwitter ;
} // end of wtkHtmShowTwitter

/**
 * Add the 'AddThis' Follow Us to your website
 *
 * Most of time should instead use wtkSocialSmartLayer above
 * Adds necessary Javascript to page just before </head>
 *
 * @param  string $fncAddThisID  is the AddThis unique identifier you get when signing up with their service
 * @param  string $fncFaceBookID the Facebook page link
 * @param  string $fncTwitterID  the Twitter account ID
 *
 * @return string the HTML that will contain the "Follow Us" code
 */
function wtkSocialFollowUs($fncAddThisID, $fncFaceBookID, $fncTwitterID) {
    // ABS 08/25/13  Most of time should instead use wtkSocialSmartLayer above
    // FollowUs from AddThis
    global $pgAddThisJS;
    if ($pgAddThisJS == false):
        $pgAddThisJS = true;
        wtkSearchReplace('</head>', '<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=' . $fncAddThisID . '"></script>' . "\n" . '</head>');
    endif;  // $pgAddThisJS == false
    $fncResult  = '<p>Follow Us</p>' . "\n" . '<div class="addthis_toolbox addthis_32x32_style addthis_default_style">' . "\n";
    $fncResult .= '<a class="addthis_button_facebook_follow" addthis:userid="' . $fncFaceBookID . '"></a>' . "\n";
    $fncResult .= '<a class="addthis_button_twitter_follow" addthis:userid="' . $fncTwitterID . '"></a>' . "\n";
    $fncResult .= '</div>' . "\n";
    $fncResult .= '<!-- AddThis Follow END -->' . "\n";
    return $fncResult;
}  // end of wtkSocialFollowUs

/**
 * Add the 'AddThis' small footprint sharing links widget to your website
 *
 * Most of time should instead use wtkSocialSmartLayer above instead
 * Adds necessary Javascript to page just before </head>
 *
 * Displays Social Media bar (facebook, emailthis, del.icio.us, etc.)
 * Specify 'top' and/or 'footer' (if you want it in both places) for
 *     $fncLocation variable.
 *
 * Variables to add to the calling page for site specificity:
 *
 *     1. Email subject and email body
 *
 *     2. Facebook link
 *
 * Add these images to the images/ folder off the root:
 *
 *     1. delicious.gif
 *
 *     2. emailthis.png
 *
 *     3. googlesbm.png
 *
 *  Add these styles to the site's style sheet and adapt as needed:
 *
 *      .facebook_top { width: 215px; float: left; clear:both; font: 10px Verdana, Arial, Helvetica, sans-serif; margin: 10px 0 10px 0; text-align: left; }
 *
 *      .facebook_footer { font: 10px Verdana, Arial, Helvetica, sans-serif; margin: 10px 0 10px 0; text-align: left; display: inline;}
 *
 *  Example on how to call this function:
 *
 *      $pgEmailSubject = wtkHtmlEncode("My Wizard's Toolkit Web Site");
 *
 *      $pgEmailBody    = wtkHtmlEncode('I built this in less time than you would guess. Take a look!');
 *
 *      $pgFacebookURL  = 'http://www.facebook.com/Mark';
 *
 *      wtkSearchReplace("<!-- @Social@ -->", wtkHtmShowSocial('top', $pgEmailSubject, $pgEmailBody, $pgFacebookURL));
 *
 * @param  string $fncLocation  'top' or 'footer' location
 * @param  string $fncEmailSubject Default Subject for email option
 * @param  string $fncEmailBody  link to your Facebook account
 * @param  string $fncFacebookURL  link to your Facebook account
 *
 * @return string the HTML that will contain the widget code
 */
function wtkHtmShowSocial($fncLocation) {
    switch ($fncLocation) :
        case 'top' :
            $loc = "facebook_top";
            break;
        case 'footer' :
            $loc = "facebook_footer";
            break;
    endswitch;
    $fncSocial  = '<div class="' . $loc . '">' . "\n";
    $fncSocial .= wtkLang('Add us to your favorites') . ':<br>' . "\n";
    $fncSocial .= '     <div style="margin-top:2px; display: inline;">' . "\n";
    $fncSocial .= '<!-- AddThis Button BEGIN -->' . "\n";
    $fncSocial .= '<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=300&amp;pubid=ra-521abaf32cb580b6"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a>' . "\n";
    $fncSocial .= '<script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>' . "\n";
    $fncSocial .= '<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-521abaf32cb580b6"></script>' . "\n";
    $fncSocial .= '<!-- AddThis Button END -->' . "\n";
    /* ------------------------------------
function wtkHtmShowSocial($fncLocation, $fncEmailSubject, $fncEmailBody, $fncFacebookURL) {
    $fncSocial .= '         <!-- AddThis Button -->' . "\n";
    $fncSocial .= '         <a href="http://www.addthis.com/bookmark.php?v=250&amp;pub=xa-4a7c06cd524f654b" onmouseover="return addthis_open(this, \'\', \'[URL]\', \'[TITLE]\')" onmouseout="addthis_close()" onclick="return addthis_sendto()"><img src="http://s7.addthis.com/static/btn/lg-share-en.gif"
    $fncSocial .= '         <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?pub=xa-4a7c06cd524f654b"></script>' . "\n";
    $fncSocial .= '         <!-- Mail -->' . "\n";
    $fncSocial .= '         <script language="Javascript">' . "\n";
    $fncSocial .= '         <!--' . "\n";
    $fncSocial .= '         if (navigator.userAgent.indexOf("Macintosh") != -1) {' . "\n";
    $fncSocial .= '            document.write("<a href=\"mailto:?subject=&amp;Body=\"><img src=\"images/emailthis.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\" title=\"\"></a>");' . "\n";
    $fncSocial .= '             }else{' . "\n";
    $fncSocial .= '            document.write("<a href=\"mailto:?subject=' . $fncEmailSubject . '&amp;Body=' . $fncEmailBody . '"><img src=\"images/emailthis.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Tell a friend\" title=\"Tell a Friend\"></a>");' . "\n";
    $fncSocial .= '           }' . "\n";
    $fncSocial .= '          //-->' . "\n";
    $fncSocial .= '          </script>' . "\n";
    $fncSocial .= '         <!-- del.icio.us -->' . "\n";
    $fncSocial .= '            <a href="javascript:location.href=\'http://del.icio.us/post?url=\'+encodeURIComponent(location.href)+\'&title=\'+encodeURIComponent(document.title)">' . "\n";
    $fncSocial .= '          <img src="images/delicious.gif" width="16" height="16" border="0" alt="del.icio.us" title="del.icio.us" /></a>' . "\n";
    $fncSocial .= '         <!-- Google -->' . "\n";
    $fncSocial .= '            <a href="javascript:(function(){var=window,b=document,c=encodeURIComponent,d=a.open(\'http://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk=\'+c(b.location)+\'&title=\'+c(b.title),\'bkmk_popup\',\'left=\'+((a.screenX||a.screenLeft)+10)+\',top=\'+((a.screenY||a
    $fncSocial .= '         <!-- Facebook -->' . "\n";
    $fncSocial .= '         <script language="Javascript">function fbs_click() {u=location.href;t=document.title;' . "\n";
    $fncSocial .= '             window.open("http://www.facebook.com/sharer.php?u="+encodeURIComponent(u)+\'&t=\'+encodeURIComponent(t),\'sharer\',\'toolbar=0,status=0,width=626,height=436\');return false;}' . "\n";
    $fncSocial .= '         </script>';
                        // changed above to double quotes instead of single quotes
    $fncSocial .= '<a href="#" onclick="return fbs_click()" target="_blank"><img src="http://b.static.ak.fbcdn.net/images/share/facebook_share_icon.gif?8:26981" alt="Facebook" title="Facebook" /></a>' . "\n";
              // after testing changed above to # instead of http://www.facebook.com/share.php?u=' . $fncFacebookURL . '
     --------------------------------------------------*/
    $fncSocial .= '       </div>' . "\n";
    $fncSocial .= '    </div>' . "\n";
    return $fncSocial;
} // end of wtkHtmShowSocial


function wtkPostHog($fncPageTitle, $fncDistinctId, $fncEvent = '~pageview'){
// https://posthog.com/docs/api/post-only-endpoints
	global $gloDeviceType, $gloMyPage, $gloWebBaseURL, $gloCurrentPage, $gloPostHog;
	if ($gloDeviceType == 'computer'):
		$gloDeviceType = 'Desktop';
	endif;

	$fncHeaders = array('Content-Type: application/json');

	$fncExtras = '';
	$fncBrowserArray = get_browser(null, true); //this function requires php_browscap.ini installed on the server.  Some servers have it but is not php default
	if ($fncBrowserArray != ''):
		$fncPlatform = isset($fncBrowserArray['platform']) ? substr($fncBrowserArray['platform'], 0, 25) : 'NULL';
		$fncBrowser = isset($fncBrowserArray['browser']) ? substr($fncBrowserArray['browser'], 0, 20) : 'NULL';
		$fncBrowserVer = isset($fncBrowserArray['version']) ? substr($fncBrowserArray['version'], 0, 12) : 'NULL';

		if ($fncPlatform != 'NULL'):
			$fncExtras .= ',' . "\n" . '  "$os": "' . $fncPlatform . '"';
		endif;
		if ($fncBrowser != 'NULL'):
			$fncExtras .= ',' . "\n" . '  "$browser": "' . $fncBrowser . '"';
		endif;
		if ($fncBrowserVer != 'NULL'):
			$fncExtras .= ',' . "\n" . '  "$browser_version": ' . $fncBrowserVer ;
		endif;
	endif;
	$fncFullURL = $gloWebBaseURL . $gloCurrentPage;
	$fncReferer = wtkGetServer('HTTP_REFERER');
	if ($fncReferer != ''):
		$fncExtras .= ',' . "\n" . '  "$referrer": "' . $fncReferer . '"';
	endif;

	$fncFields =<<<htmVAR
{
"event": "~pageview",
"api_key": "$gloPostHog",
"distinct_id": "$fncDistinctId",
"properties": {
  "title": "$fncPageTitle",
  "~device_type": "$gloDeviceType",
  "~host": "wizardstoolkit.com",
  "~current_url": "$fncFullURL",
  "~pathname": "$gloMyPage"$fncExtras
  }
}
htmVAR;

	$fncFields = wtkReplace($fncFields, '"~','"$');

	$ch = curl_init();
	curl_setopt( $ch,CURLOPT_URL, 'https://app.posthog.com/capture/' );
	curl_setopt( $ch,CURLOPT_POST, true );
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $fncHeaders );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, $fncFields );
	$fncResult = curl_exec($ch );
	curl_close( $ch );
} // wtkPostHog
?>
