<?php
$pgSecurityLevel = 1;
require('wtk/wtkLogin.php');

if ($gloDeviceType == 'phone'):
    $pgHpad = ' style="padding-left:18px"';
    $pgBR = '';
    $pgHtm =<<<htmVAR
<h4 style="padding-left:18px">Video Tutorials</h4>
htmVAR;
else:
    $pgHpad = '';
    $pgBR = '<br>';
    $pgHtm =<<<htmVAR
<div class="container">
    <h4>Video Tutorials</h4>
    <div class="wtk-list card b-shadow">
htmVAR;
endif;
$pgHtm .= "\n";
$pgHtm .=<<<htmVAR
        <br><h5$pgHpad>Docker 2-minute Setup</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/3myhUcsz9w8?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>MAMP Setup and Features</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/bvldASvDtJM?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>SQL Report Wizard</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/mr234KXTVJ0?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>WTK Page Builder</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/bSHjPLpMdiY?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>Browse Box</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/zRcGPN8yCJs?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>Images in Browse List</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/wLOEfv92Mkw?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>CSS Maker</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/6HikuWO6ZuE?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>Single Page App vs Multi Page App</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/MxgICh0RpZg?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>Menu Sets</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/Yn6U1pUP7dQ?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <br><br><h5$pgHpad>File Display</h5>$pgBR
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/pIJfRXWncQQ?origin=https://wizardstoolkit.com&modestbranding=1&rel=0&playsinline=0" title="video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
htmVAR;
if ($gloDeviceType != 'phone'):
    $pgHtm .= "\n" . '    </div>' . "\n";
    $pgHtm .= '</div>';
endif;

wtkAddUserHistory('Video List');
echo $pgHtm;
exit;
?>
