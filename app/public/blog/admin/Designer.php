<?php
/*
MIT License

Copyright 2023 Wizard's Toolkit

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

----------------------------------------------------------------
This file was created by the makers of the Wizard's Toolkit
https://WizardsToolkit.com
Wizard's Toolkit is a low-code development library for PHP, SQL and JavaScript
----------------------------------------------------------------

This file should not be deployed to live server.  If it is then you should consider
adding security to it so it is only accessible by users with a security login.
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Wizard's Toolkit</title>
    <link rel="shortcut icon" href="/imgs/favicon/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/wtk/css/materialize.min.css">
    <link rel="stylesheet" href="../files/blog.css">
  <script type="text/javascript" src="/wtk/js/jquery.min.js" defer></script>
  <script type="text/javascript" src="/wtk/js/materialize.min.js" defer></script>
  <script type="text/javascript" src="/wtk/js/jscolor.js" defer></script>
  <script type="text/javascript" src="../files/blog.js" defer></script>
</head>
<body onload="Javascript:wtkStart('Designer')" id="blogBody">
    <div class="container">
		<div class="card">
		    <div class="card-content">
		        <h3>Blogging with <small><a href="https://wizardstoolkit.com/">Wizard&rsquo;s Toolkit</a></small></h3><br>
		        <p>Set the CSS variables which will affect how all elements on your page look. For help with trending
		            color palettes, check out <a target="_blank" href="https://coolors.co/palettes/trending">coolors.co</a>.</p>
		        <form id="cssForm" method="post">
		            <div class="row">
		                <div class="input-field col m3 s12">
		                    <select id="blogFont" name="blogFont" class="browser-default selFont">
		                        <option value="Arial" style="font-family:'Arial'">Arial</option>
		                        <option value="Arial Black" style="font-family:'Arial Black'">Arial Black</option>
		                        <option value="Arial Narrow" style="font-family:'Arial Narrow'">Arial Narrow</option>
		                        <option value="Bookman" style="font-family:'Bookman'">Bookman</option>
		                        <option value="Brush Script MT" style="font-family:'Brush Script MT'">Brush Script</option>
		                        <option value="Calibri" style="font-family:'Calibri'">Calibri</option>
		                        <option value="Cambria" style="font-family:'Cambria'">Cambria</option>
		                        <option value="Candara" style="font-family:'Candara'">Candara</option>
		                        <option value="Comic Sans MS" style="font-family:'Comic Sans MS'">Comic Sans</option>
		                        <option value="Copperplate" style="font-family:'Copperplate'">Copperplate</option>
		                        <option value="Courier New" style="font-family:'Courier New'">Courier New</option>
		                        <option value="Didot" style="font-family:'Didot'">Didot</option>
		                        <option value="Garamond" style="font-family:'Garamond'">Garamond</option>
		                        <option value="Geneva" style="font-family:'Geneva'">Geneva</option>
		                        <option value="Georgia" style="font-family:'Georgia'">Georgia</option>
		                        <option value="Helvetica" style="font-family:'Helvetica'">Helvetica</option>
		                        <option value="Lucida Bright" style="font-family:'Lucida Bright'">Lucida Bright</option>
		                        <option value="Monaco" style="font-family:'Monaco'">Monaco</option>
		                        <option value="Optima" style="font-family:'Optima'">Optima</option>
		                        <option value="Palatino" style="font-family:'Palatino'">Palatino</option>
		                        <option value="Perpetua" style="font-family:'Perpetua'">Perpetua</option>
		                        <option value="Tahoma" style="font-family:'Tahoma'">Tahoma</option>
		                        <option value="Times" style="font-family:'Times'">Times</option>
		                        <option value="Trebuchet MS" style="font-family:'Trebuchet MS'">Trebuchet</option>
		                        <option value="Verdana" style="font-family:'Verdana'">Verdana</option>
		                    </select>
		                    <label for="blogFont" class="active">Blog Font</label>
		                </div>
		            <!--
		                <div class="input-field col m2 s12">
		                    <input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--gradient-top" name="--gradient-top" value="">
		                    <label class="active" for="--gradient-top">Gradient Top</label>
		                </div>
		                <div class="input-field col m2 s12">
		                    <input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--gradient-bottom" name="--gradient-bottom" value="">
		                    <label class="active" for="--gradient-bottom">Gradient Bottom</label>
		                </div>
		            -->
		                <div class="input-field col m2 s12">
		                    <input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--wtk-blog-header" name="--wtk-blog-header" value="">
		                    <label class="active" for="--wtk-blog-header">Header</label>
		                </div>
		                <div class="input-field col m2 s12">
		                    <input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--wtk-blog-nav" name="--wtk-blog-nav" value="">
		                    <label class="active" for="--wtk-blog-nav">Nav Bar</label>
		                </div>
		                <div class="input-field col m2 s12">
		                    <input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--wtk-blog-main" name="--wtk-blog-main" value="">
		                    <label class="active" for="--wtk-blog-main">Main</label>
		                </div>
		                <div class="input-field col m2 s12">
		                    <input type="text" class="form-input w72" oninput="JavaScript:setCssRoot(this.id, this.jscolor)" id="--wtk-blog-footer" name="--wtk-blog-footer" value="">
		                    <label class="active" for="--wtk-blog-footer">Footer</label>
		                </div>
		                <div class="col m3 s12">
		                    <button type="button" class="btn tooltipped" onclick="JavaScript:saveCSS()" data-tooltip="Save to blog.css File">Save File</button>
		                </div>
						<div class="col m3 s12">
		                    <a class="btn blue tooltipped" href="index.php" data-tooltip="Blog List - start blogging!">Blog List</a>
		                </div>
		            </div>
		        </form>
			</div>
		</div>
	</div>
<hr width="100%">
	<header>
		@CompanyLogo@ <h2 style="display: inline">@CompanyName@ <small>My Blog</small></h2>
	</header>
    <nav>
        <p>Links to your<br>
            Blogs will be here</p>
    </nav>
    <main>
        <h1>Your Blog Title</h1>
        <p>The content of your blog or article will go here. It can be as long as you want.</p>
    </main>
	<footer>&copy @CurrentYear@ @CompanyName@</footer>
	<!-- preloader -->
	<div id="plsWait" class="modal wrapper-load center-align">
		<div class="preloader-wrapper medium-size active">
			<div class="spinner-layer spinner-custom">
				<div class="circle-clipper left">
					<div class="circle"></div>
				</div>
				<div class="gap-patch">
					<div class="circle"></div>
				</div>
				<div class="circle-clipper right">
					<div class="circle"></div>
				</div>
			</div>
		</div>
	</div>
	<!-- end preloader -->
	<div id="modalAlert" class="modal">
		<div class="modal-content card center">
			<i id="modIcon" class="material-icons large red-text text-darken-1">warning</i>
			<h4 id="modHdr">Ooops!</h4>
			<p id="modText"></p>
			<a class="btn b-shadow center modal-close waves-effect">Close</a>
		</div>
	</div>
</body>
</html>
