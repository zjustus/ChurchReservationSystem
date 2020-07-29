qrcode = window.qrcode;

const video = document.createElement("video");
const canvasElement = document.getElementById("qr-canvas");
const canvas = canvasElement.getContext("2d");

let scanning = false;

qrcode.callback = res => {
  if (res) {
	video.srcObject.getTracks().forEach(track => {
		track.stop();
	});
	scanning = false;
	canvasElement.hidden = true;

	$("#tokenInput").val(res);
	$("#qrInputForm").submit();
  }
};

$("#btn-scan-qr").click(function(){
	navigator.mediaDevices
	.getUserMedia({ video: { facingMode: "environment" } })
	.then(function(stream) {
	  scanning = true;
	  $("#inputForm-container").hide();
	  $("#btn-scan-qr").hide();
	  $("#qr-cancel").show();
	  canvasElement.hidden = false;
	  video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
	  video.srcObject = stream;
	  video.play();
	  tick();
	  scan();
	});
});
$("#qr-cancel").click(function(){
	video.srcObject.getTracks().forEach(track => {
		track.stop();
	});
	scanning = false;
	$("#btn-scan-qr").show();
	$("#inputForm-container").show();
	$("#qr-cancel").hide();
	canvasElement.hidden = true;
});

function tick() {
  canvasElement.height = video.videoHeight;
  canvasElement.width = video.videoWidth;
  canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);

  scanning && requestAnimationFrame(tick);
}

function scan() {
  try {
	qrcode.decode();
  } catch (e) {
	setTimeout(scan, 300);
  }
}
