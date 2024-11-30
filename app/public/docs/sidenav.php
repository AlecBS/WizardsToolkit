<?php
function wtkGetPost($fncParameter, $fncDefault = '') {
    $fncResult = isset($_POST[$fncParameter]) ? $_POST[$fncParameter] : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;  // $fncResult == ''
    return $fncResult;
} // end of wtkGetPost
function wtkReplace($fncSubject, $fncSearchFor, $fncReplaceWith) {
    $fncResult = $fncSubject;
    if (is_array($fncResult)):
        foreach ($fncResult as &$fncInnerArray):
            $fncInnerArray = wtkReplace($fncInnerArray, $fncSearchFor, $fncReplaceWith);
        endforeach;
        unset($fncInnerArray);
        return $fncResult;
    else:   // Not is_array($fncResult)
        return str_replace($fncSearchFor, $fncReplaceWith, $fncSubject);
    endif;  // is_array($fncResult)
} // wtkReplace

$pgDir = wtkGetPost('dir');

$pgHtm =<<<htmVAR
    <h5><a href="/"><em>back to main website</em></a></h5>
    <h6><a href="https://wizardstoolkit.com/wtk.php" target="_blank">WTK Demo</a></h6>

    <section class="phpdocumentor-sidebar__category">
        <h2 class="phpdocumentor-sidebar__category-header">Download and Setup</h2>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="setup.html">Installation Instructions</a></h3>
    </section>
    <section class="phpdocumentor-sidebar__category">
        <h2 class="phpdocumentor-sidebar__category-header">Design Methodology</h2>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="menus.html">Data-Driven Menus</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="database.html">Database Conventions</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="spa-vs-mpa.html#mpa">Multi Page App</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="spa-vs-mpa.html#spa">Single Page App</a></h3>
    </section>
    <section class="phpdocumentor-sidebar__category">
        <h2 class="phpdocumentor-sidebar__category-header">Basic Features</h2>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="email.html">Emailing</a></h3>
    </section>
    <section class="phpdocumentor-sidebar__category">
        <h2 class="phpdocumentor-sidebar__category-header">WTK Core Library</h2>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-browse.html">Browse</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-chart.html">Chart</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-clientfuncs.html">Client Functions</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-core.html">Core</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-datapdo.html">DataPDO</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-ecommerce.html">Ecommerce</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-email.html">Email</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-encrypt.html">Encrypt</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-form.html">Form</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-google.html">Google</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-html.html">Html</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-image.html">Image</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-legacy.html">Legacy</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-materialize.html">Materialize</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-mobile.html">Mobile</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-print.html">Print</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-save.html">Save</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-security.html">Security</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-social.html">Social</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-time.html">Time</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-twilio.html">Twilio</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="files/wtk-lib-utils.html">Utils</a></h3>
    </section>
    <section class="phpdocumentor-sidebar__category">
        <h2 class="phpdocumentor-sidebar__category-header">Miscellaneous</h2>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="https://wizardstoolkit.com/wtk.php" target="_blank">WTK Demo</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="https://wizardstoolkit.com/wiki/" target="_blank">WTK Wiki</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="https://wizardstoolkit.com/wtk/contactUs.php" target="_blank">Contact Us</a></h3>
        <h3 class="phpdocumentor-sidebar__root-package"><a href="/wtk/css/" target="_blank">CSS Maker</a></h3>
    </section>
htmVAR;
if ($pgDir != 'root'):
    $pgHtm = wtkReplace($pgHtm, 'href="menus.html', 'href="../menus.html');
    $pgHtm = wtkReplace($pgHtm, 'href="database.html', 'href="../database.html');
    $pgHtm = wtkReplace($pgHtm, 'href="email.html', 'href="../email.html');
    $pgHtm = wtkReplace($pgHtm, 'href="s', 'href="../s');
    $pgHtm = wtkReplace($pgHtm, 'href="files/wtk-lib', 'href="wtk-lib');
endif;
echo $pgHtm;
?>
