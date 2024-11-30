<div class="container">
	<div class="card">
		<div class="card-content">
			<h2>Microphone<small><br>and SDK Testing</small></h2>
			<p>This is only meant to be tested using an app built with Xcode
				and the Wizard&rsquo;s Toolkit library.</p>
			<br>
			<button id="check-speech-recognition" class="btn">
				Check Speech Recognition Availability
			</button><br><br>
			<button id="start-speech-recognition" class="btn">Start Speech Recognition</button>
			<br><br>
			<button id="stop-speech-recognition" class="btn">Stop Speech Recognition</button>
			<br><br>
			<button id="get-app-version" class="btn">Get App Version</button>
			<hr>
			<p>Speech Recognition Status:
				<b><span id="speech-recognition-status"></span></b>
			</p>
			<p>Speech Recognition Running Status:
				<b><span id="speech-recognition-running-status"></span></b>
			</p>
			<p>Speech Recognition Error:
				<b><span id="speech-recognition-error"></span></b>
			</p>
			<p>APP Version:
				<b><span id="app-version"></span></b>
			</p>
			<div id="output">
				<p>
					Recognized Text:
					<span id="recognized-text"></span>
				</p>
			</div>
			<hr><h3>Debug Log</h3>
			<div id="debugLogDIV"></div>
		</div>
	</div>
</div>

<script type="text/javascript">
pgDebug = 'Y';
const wtkSDK = new window.WTKSDK();

(async function() {
	// Register event listeners
	wtkSDK.on("updateRecognizedText", (data) => {
		document.getElementById("recognized-text").innerText =
			data.formattedString;
			wtkDebugLog('updateRecognizedText: ' + data.formattedString);
	});

	wtkSDK.on("updateSpeechRecognitionAvailability", (data) => {
		document.getElementById("speech-recognition-status").innerText =
			data.isAvailable ? "Available" : "Not Available";
	});

	wtkSDK.on("handleSpeechRecognitionError", (data) => {
		console.error("Speech Recognition Error:", data.error);
		document.getElementById("speech-recognition-error").innerText =
			data.error;
	});

	wtkSDK.on("updateRecognitionState", (data) => {
		document.getElementById("speech-recognition-running-status").innerText =
			data.isRecognizing ? "Running" : "Not Running";
	});

	// Example method calls using async/await
	document
		.getElementById("check-speech-recognition")
		.addEventListener("click", async () => {
			wtkDebugLog('Check speech recognition clicked');
			try {
				const isAvailable = await wtkSDK.isSpeechRecognitionAvailable();
				document.getElementById("speech-recognition-status").innerText =
					isAvailable ? "Available" : "Not Available";
			} catch (error) {
				console.error(
					"Error checking speech recognition availability:",
					error
				);
			}
		});

	document
		.getElementById("start-speech-recognition")
		.addEventListener("click", async () => {
			wtkDebugLog('Start speech recognition clicked');
			try {
				await wtkSDK.startSpeechRecognition();
			} catch (error) {
				console.error("Error starting speech recognition:", error);
			}
		});

	document
		.getElementById("stop-speech-recognition")
		.addEventListener("click", async () => {
			try {
				await wtkSDK.stopSpeechRecognition();
			} catch (error) {
				console.error("Error stopping speech recognition:", error);
			}
		});

	document
		.getElementById("get-app-version")
		.addEventListener("click", async () => {
			wtkDebugLog('Get App Version clicked');
			try {
				const version = await wtkSDK.getAppVersion();
				document.getElementById("app-version").innerText = version;
				console.log("App Version:", version);
			} catch (error) {
				console.error("Error getting app version:", error);
			}
		});
})();

</script>
