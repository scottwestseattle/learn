// set up basic variables for app

var record = null;
var stop = null;
var soundClips = null;
var canvas = null;
var mainSection = null;

// visualiser setup - create web audio api context and canvas
let audioCtx = null;
var canvasCtx = null;


///////////////////////////////////////////////////
// main block for doing the audio recording
///////////////////////////////////////////////////

const constraints = { audio: true };
var _audio = null;
var _mediaRecorder = null;

function startMediaRecorder()
{
    console.log('startMediaRecorder()...');

if (navigator.mediaDevices.getUserMedia) {
  console.log('getUserMedia supported.');

  let chunks = [];

  let onSuccess = function(stream) {

    console.log('onSuccess');

    _mediaRecorder = new MediaRecorder(stream);

    visualize(stream);

    _mediaRecorder.onstop = function(e) {
      console.log("data available after MediaRecorder.stop() called.");

      //const clipName = prompt('Enter a name for your sound clip?','My unnamed clip');
      const clipName = null;

      var clipContainer = document.querySelector('article');
      if (clipContainer == null)
        clipContainer = document.createElement('article');

      const clipLabel = document.createElement('p');
      const audio = document.createElement('audio');
      const deleteButton = document.createElement('button');

      clipContainer.classList.add('clip');
      audio.setAttribute('controls', '');
      deleteButton.textContent = 'Delete';
      deleteButton.className = 'delete';

      if(clipName === null) {
        clipLabel.textContent = '';
      } else {
        clipLabel.textContent = clipName;
      }

      clipContainer.appendChild(audio);
      clipContainer.appendChild(clipLabel);
      clipContainer.appendChild(deleteButton);
      soundClips.appendChild(clipContainer);

      audio.controls = true;
      const blob = new Blob(chunks, { 'type' : 'audio/ogg; codecs=opus' });
      chunks = [];
      const audioURL = window.URL.createObjectURL(blob);
      audio.src = audioURL;
      console.log("recorder stopped");

      deleteButton.onclick = function(e) {
        let evtTgt = e.target;
        evtTgt.parentNode.parentNode.removeChild(evtTgt.parentNode);
      }

      clipLabel.onclick = function() {
        const existingName = clipLabel.textContent;
        const newClipName = prompt('Enter a new name for your sound clip?');
        if(newClipName === null) {
          clipLabel.textContent = existingName;
        } else {
          clipLabel.textContent = newClipName;
        }
      }

      audio.play();
      _audio = audio;
    }

    _mediaRecorder.ondataavailable = function(e) {
      chunks.push(e.data);
    }

    setTimeout(runRecord, 500)

    console.log('end of onSuccess');
  } // end of onSuccess

  let onError = function(err) {
    console.log('The following error occured: ' + err);
  }

    console.log('starting getUserMedia... ');

    // this triggers the permission dialog
    navigator.mediaDevices.getUserMedia(constraints).then(onSuccess, onError);

    console.log('end of getUserMedia init');

} else {
   console.log('getUserMedia not supported on your browser!');
}

} // end of function

function startRecording()
{
    console.log('startRecording...');

    if (_mediaRecorder == null)
    {
        record.style.background = "orange";
        record.textContent = "Waiting...";
        startMediaRecorder();
    }
    else
    {
        runRecord();
    }
}

function runRecord()
{
    //console.log('runRecord...');

    if (_mediaRecorder.state == "inactive")
    {
        var clips = document.querySelector('article');
        if (clips)
        {
            clips.innerHTML = "";
        }

        try {
            _mediaRecorder.start();
            //console.log(_mediaRecorder.state);
            //console.log("recordering...");
            record.style.background = "red";
            record.textContent = "Stop";
        }
        catch(e)
        {
            console.log(_mediaRecorder.state);
            console.log(e)
        }
    }
    else
    {
        try {
            _mediaRecorder.stop();
            //console.log(_mediaRecorder.state);
            //console.log("recorder started");
            record.style.background = "#0088cc";
            record.textContent = "Record";
        }
        catch(e)
        {
            console.log(_mediaRecorder.state);
            console.log(e)
        }
    }
}

function visualize(stream) {
  if(!audioCtx) {
    audioCtx = new AudioContext();
  }

  const source = audioCtx.createMediaStreamSource(stream);

  const analyser = audioCtx.createAnalyser();
  analyser.fftSize = 2048;
  const bufferLength = analyser.frequencyBinCount;
  const dataArray = new Uint8Array(bufferLength);

  source.connect(analyser);
  //analyser.connect(audioCtx.destination);

  draw()

  function draw() {
    const WIDTH = canvas.width
    const HEIGHT = canvas.height;

    requestAnimationFrame(draw);

    analyser.getByteTimeDomainData(dataArray);

    canvasCtx.fillStyle = 'rgb(200, 200, 200)';
    canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);

    canvasCtx.lineWidth = 2;
    canvasCtx.strokeStyle = 'rgb(0, 0, 0)';

    canvasCtx.beginPath();

    let sliceWidth = WIDTH * 1.0 / bufferLength;
    let x = 0;


    for(let i = 0; i < bufferLength; i++) {

      let v = dataArray[i] / 128.0;
      let y = v * HEIGHT/2;

      if(i === 0) {
        canvasCtx.moveTo(x, y);
      } else {
        canvasCtx.lineTo(x, y);
      }

      x += sliceWidth;
    }

    canvasCtx.lineTo(canvas.width, canvas.height/2);
    canvasCtx.stroke();

  }
}

window.onresize = function() {
    canvas.width = mainSection.offsetWidth;
}

function loadRecorder()
{
    console.log('loading recorder...');
    record = document.querySelector('.record');
    play = document.querySelector('.play');
    soundClips = document.querySelector('.sound-clips');
    canvas = document.querySelector('.visualizer');
    mainSection = document.querySelector('.main-controls');

    canvasCtx = canvas.getContext("2d");

    // disable play button while not recording
    //play.disabled = true;

    // size our window
    window.onresize();
}
