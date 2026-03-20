/**
 * Background — Subtle animated floating geometric shapes
 */
(function () {
  var canvas = document.getElementById('bg-canvas');
  if (!canvas) return;
  var ctx = canvas.getContext('2d');
  var shapes = [];
  var w, h;

  function resize() {
    w = canvas.width = window.innerWidth;
    h = canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  for (var i = 0; i < 10; i++) {
    shapes.push({
      x: Math.random() * (w || 1200),
      y: Math.random() * (h || 800),
      size: 20 + Math.random() * 50,
      speed: 0.12 + Math.random() * 0.25,
      angle: Math.random() * Math.PI * 2,
      rotSpeed: (Math.random() - 0.5) * 0.002,
      opacity: 0.015 + Math.random() * 0.025,
      sides: Math.random() > 0.5 ? 6 : 8
    });
  }

  function drawPolygon(cx, cy, r, sides, rotation) {
    ctx.beginPath();
    for (var j = 0; j <= sides; j++) {
      var a = rotation + (j * 2 * Math.PI / sides);
      var px = cx + r * Math.cos(a);
      var py = cy + r * Math.sin(a);
      if (j === 0) ctx.moveTo(px, py);
      else ctx.lineTo(px, py);
    }
    ctx.closePath();
  }

  function animate() {
    ctx.clearRect(0, 0, w, h);
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var color = isDark ? '212,175,55' : '27,67,50';

    shapes.forEach(function (s) {
      s.y -= s.speed;
      s.angle += s.rotSpeed;
      if (s.y < -s.size) {
        s.y = h + s.size;
        s.x = Math.random() * w;
      }
      ctx.save();
      ctx.globalAlpha = s.opacity;
      ctx.strokeStyle = 'rgba(' + color + ',0.6)';
      ctx.lineWidth = 0.5;
      drawPolygon(s.x, s.y, s.size, s.sides, s.angle);
      ctx.stroke();
      ctx.restore();
    });

    requestAnimationFrame(animate);
  }
  animate();
})();
