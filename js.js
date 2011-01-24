canvas = document.getElementById('canvas');
cxt = canvas.getContext('2d');
//canvas.onmousemove = function(e){console.log(e.pageX)};

canvas.onmousedown = function(e){
  var ev = e ? e : window.event;
  drawCirc(ev);
  //canvas.addEventListener('mousemove', 'drawCirc', false);
  canvas.onmousemove = drawCirc;
  console.log(mouseX+" "+mouseY);
}

canvas.onmouseup = function(){
  canvas.onmousemove = null;
}

function drawCirc(ev){
  console.log('go');

  mouseX = ev.clientX;
  mouseY = ev.clientY;

  cxt.beginPath();
  cxt.fillStyle = '#ff00bb';
  //cxt.shadowBlur = .5;
  cxt.arc(mouseX, mouseY, 10, 0, 2*Math.PI, 1);
  cxt.closePath();
  cxt.fill();
}

