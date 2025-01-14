<?PHP
// Below is affiliate program for Wizard's Toolkit.  Change for your own company details.
$gloLoginRequired = false;
require('wtk/wtkLogin.php');
$gloWTKmode = 'ADD';

$gloCoName = 'Wizard&rsquo;s Toolkit';
$pgAffiliateRate = 50; // commission you are offering affiliates
$pgSpecialRate   = 10; // commission for special promotions

$pgTmp  = wtkFormText('wtkAffiliates', 'CompanyName','text','','m6 s12', 'N', 'optional');
$pgTmp .= wtkFormText('wtkAffiliates', 'ContactName');
$pgTmp .= '</div><div class="row">' . "\n";
$pgTmp .= wtkFormText('wtkAffiliates', 'Email', 'email','Contact Email','m6 s12','Y','for payment notifications');
$pgTmp .= wtkFormText('wtkAffiliates', 'WebPasscode','text','Passcode','m4 s12', 'Y','to access affiliate account page');
$pgTmp .= wtkFormHidden('wtkMode', 'ADD');

$pgHtm =<<<htmVAR
    <br>
    <div class="card b-shadow">
        <div class="card-content">
            <h4 class="center">Join the <a target="_blank" href="/">$gloCoName</a> Affiliate Program
                <small><br>Transforming Development, Together!</small>
            </h4>
            <br>
            <form id="wtkForm" name="wtkForm" method="POST">
                <div class="row">
                    <div class="col s12">
                        <p>Welcome to the Wizard&rsquo;s Toolkit Affiliate Program
                        where you can entice more clients to hire you, enhance
                        your offerings, and earn substantial rewards!</p>
<br>
<h4>Why Partner with Wizard&rsquo;s Toolkit?</h4>
<ul class="browser-default">
    <li>Earn Generously: Enjoy a <strong class="green-text">$pgAffiliateRate% commission</strong> on the first-year subscription sales you generate</li>
    <ul class="browser-default">
        <li>Earn $pgSpecialRate% on special promotions like <a target="_blank" href="startup.php">Startup/MVP Specials</a>.</li>
    </ul>
    <li>Clients Perceive Value: Offer your clients an exclusive 10% discount on WTK subscription.</li>
    <li>Enhance Your Portfolio: By recommending WTK, you position your company as a leader in cutting-edge low-code development, showcasing innovation and efficiency to your clients.</li>
    <li>Drive Client Success: WTK&rsquo;s low-code solutions accelerate project timelines and reduce costs, making you the hero for your clients.</li>
</ul>
<br>
<h4>Unlock New Opportunities</h4>
<ul class="browser-default">
    <li>Expand Your Services: Broaden your offerings by integrating WTK into
        your projects, gaining more billable hours and client trust.</li>
    <ul class="browser-default">
        <li>With minimum training you can learn user, widget dashboard, and menu navigation management.</li>
        <li>If you have even minimal SQL skills, you can easily create reports and widgets.</li>
        <li>If you have PHP skills you will have unlimited hours helping your client grow their web empire.</li>
    </ul>
    <li>Increase Client Satisfaction: Deliver faster, smarter solutions that
        meet the evolving needs of your clients.</li>
    <li>Mobile App Ready: Wizard&rsquo;s Toolkit websites have JavaScript built-in to work with
        Xcode and Swift to make <a target="_blank" href="/mobile.php">iPhone apps</a>!
         Almost no effort at all and you can provide your clients with a mobile app.</li>
</ul>
<br>
<h4>Leverage the Low-Code Craze</h4>
<p>Your clients have heard of &ldquo;low-code&rdquo; or &ldquo;no-code&rdquo;
  development. Most of these offerings are extremely limiting, requiring
  hosting on their servers and lacking flexibility. WTK allows you to deploy
  to any server and customize as much as you like. It&rsquo;s your code. Use this
  flexibility to win big contracts and outperform competitors.</p>
<br>
<h4>Join Our Community</h4>
<ul class="browser-default">
    <li>Training and Support: We offer comprehensive training for your or your
     team to master WTK, ensuring smooth implementation and client satisfaction.</li>
    <li>Stay Ahead of the Curve: Be at the forefront of technology, offering
      your clients the latest in development efficiency and innovation.</li>
</ul>
<br>
<h4>Ready to Get Started?</h4>
<p>Fill out the form below to become a Wizard&rsquo;s Toolkit affiliate.
 Let&rsquo;s revolutionize development together and create more success stories!
 When you sign up you will immediately receive a unique link associated with
 your account.  When a prospect uses your link, any purchase they make
 within the next 90 days will be credited to your account.</p>

                    </div>
                    $pgTmp
                    <div class="center">
                        <a class="waves-effect waves-light btn-large green" onclick="JavaScript:addAffiliate()">Become an Affiliate</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<br><br>
<script type="text/javascript">
function addAffiliate(){
    if (wtkRequiredFieldsFilled('wtkForm')){
        waitLoad('on');
        let fncFormData = $('#wtkForm').serialize();
        $.ajax({
            type: 'POST',
            url: '/wtk/ajxSaveAffiliate.php',
            data: (fncFormData),
            success: function(data) {
                waitLoad('off');
                let fncJSON = $.parseJSON(data);
                if (fncJSON.result == 'ok'){
                    window.location.replace('affiliate.php?rng=' + fncJSON.hash);
                }
            }
        })
    }
}
</script>
htmVAR;

wtkSearchReplace('wtkBlue.css','wtkGreen.css');
wtkMergePage($pgHtm, 'Wizards Toolkit', 'wtk/htm/mpa.htm');
?>
