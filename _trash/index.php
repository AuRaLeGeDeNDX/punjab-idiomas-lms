<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Punjab Idiomas | Escuela de Español en Barcelona</title>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;scroll-behavior:smooth;}
body{
font-family:'Poppins',sans-serif;
background:#0f0f14;
color:white;
overflow-x:hidden;
cursor:none;
}

/* ================= CURSOR ================= */
.cursor-dot,.cursor-outline{
position:fixed;top:0;left:0;
transform:translate(-50%,-50%);
pointer-events:none;z-index:9999;
border-radius:50%;
}
.cursor-dot{width:8px;height:8px;background:#ff6a00;}
.cursor-outline{
width:40px;height:40px;
border:2px solid #ff6a00;
transition:.15s ease-out;
}

/* ================= NAVBAR ================= */
nav{
display:flex;justify-content:space-between;align-items:center;
padding:20px 8%;
position:fixed;width:100%;
background:rgba(0,0,0,.7);
backdrop-filter:blur(12px);
z-index:1000;
}
.logo{display:flex;align-items:center;gap:10px;cursor:pointer;}
.logo img{height:45px;}
.logo span{
font-family:'Orbitron';
font-size:22px;font-weight:700;
color:#ff6a00;
}
nav ul{display:flex;gap:30px;list-style:none;align-items:center;}
nav ul li a{color:white;text-decoration:none;transition:.3s;}
nav ul li a:hover{color:#ff6a00;}
.highlight-btn{
background:#ff6a00;padding:8px 20px;border-radius:30px;
}
.highlight-btn:hover{background:#ff8c1a;}

/* ================= HERO ================= */
.hero{
height:100vh;position:relative;
display:flex;align-items:center;justify-content:center;text-align:center;
}
.hero video{
position:absolute;width:100%;height:100%;
object-fit:cover;z-index:-2;
}
.hero::after{
content:'';position:absolute;width:100%;height:100%;
background:rgba(0,0,0,.65);z-index:-1;
}
.hero h1{
font-family:'Orbitron';
font-size:60px;
background:linear-gradient(90deg,#ff6a00,#ff9c33);
-webkit-background-clip:text;color:transparent;
animation:glow 3s infinite alternate;
}
@keyframes glow{
from{text-shadow:0 0 10px #ff6a00;}
to{text-shadow:0 0 30px #ff9c33;}
}
.hero p{margin-top:20px;color:#ccc;max-width:700px;margin:auto;}
.btn{
display:inline-block;margin-top:30px;
padding:14px 30px;border-radius:40px;
text-decoration:none;font-weight:600;transition:.3s;
}
.btn-primary{
background:#ff6a00;color:white;
box-shadow:0 0 20px #ff6a00;
}
.btn-primary:hover{background:#ff8c1a;}

/* ================= SECTION ================= */
section{padding:120px 10%;}
.section-title{text-align:center;margin-bottom:60px;}
.section-title h2{
font-family:'Orbitron';color:#ff6a00;font-size:36px;
}

/* ================= ABOUT ================= */
.about-container{
display:flex;flex-wrap:wrap;gap:60px;align-items:center;
}
.about-images{flex:1;position:relative;}
.about-images img{
width:100%;border-radius:20px;
box-shadow:0 0 20px rgba(255,106,0,.3);
}
.about-img-2{
position:absolute;bottom:-40px;right:-40px;width:60%;
}
.about-contact-box{
position:absolute;top:20px;left:20px;
background:#ff6a00;padding:15px 20px;border-radius:12px;
}
.about-content{flex:1;}
.about-content h3{color:#ff6a00;margin-top:20px;}

/* ================= SERVICES ================= */
.services{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
gap:30px;
}
.service-card{
background:rgba(255,255,255,.05);
padding:30px;border-radius:20px;
transition:.4s;border:1px solid rgba(255,255,255,.08);
}
.service-card:hover{
transform:translateY(-10px);
box-shadow:0 0 25px #ff6a00;
}

/* ================= CONTACT ================= */
.contact-box{
background:rgba(255,255,255,.05);
padding:40px;border-radius:20px;text-align:center;
}

/* ================= FOOTER ================= */
footer{
padding:40px;text-align:center;
background:#0b0b0f;color:#777;
}

/* ================= REVEAL ================= */
.reveal{
opacity:0;transform:translateY(60px);
transition:all .8s ease;
}
.reveal.active{
opacity:1;transform:translateY(0);
}
</style>
</head>

<body>

<div class="cursor-dot"></div>
<div class="cursor-outline"></div>

<nav>
<div class="logo" onclick="window.location.href='index.php'">
<img src="logo.jpeg">
<span>Punjab Idiomas</span>
</div>
<ul>
<li><a href="#about">Sobre Nosotros</a></li>
<li><a href="#courses">Cursos</a></li>
<li><a href="#dele">DELE</a></li>
<li><a href="#contact">Contacto</a></li>
<li><a href="/login">Login</a></li>
<li><a href="/register" class="highlight-btn">Register</a></li>
</ul>
</nav>

<!-- HERO -->
<div class="hero">
<video autoplay muted loop>
<source src="https://cdn.coverr.co/videos/coverr-students-in-classroom-1574/1080p.mp4" type="video/mp4">
</video>
<div>
<h1>Domina el Español. Gana Confianza.</h1>
<p>Centro especializado en preparación oficial DELE A1–B2 en Barcelona.</p>
<a href="/register" class="btn btn-primary">Inscribirse</a>
</div>
</div>

<!-- ABOUT -->
<section id="about">
<div class="section-title reveal"><h2>Sobre Nosotros</h2></div>
<div class="about-container">
<div class="about-images reveal">
<img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f">
<div class="about-img-2">
<img src="https://images.unsplash.com/photo-1513258496099-48168024aec0">
</div>
<div class="about-contact-box">
📞 +34 612 45 50 57
</div>
</div>

<div class="about-content reveal">
<p>
Punjab Idiomas es una escuela de español en Barcelona creada para ayudarte a aprender el idioma con seguridad y prepararte para los exámenes oficiales DELE.
Ofrecemos enseñanza estructurada desde A1 hasta B2.
</p>

<h3>Misión</h3>
<p>Formar estudiantes con bases sólidas y acompañamiento constante para aprobar el DELE con éxito.</p>

<h3>Visión</h3>
<p>Ser una de las escuelas más confiables de España en enseñanza teórica del español.</p>

<a href="/about" class="btn btn-primary">Más Información</a>
</div>
</div>
</section>

<!-- COURSES -->
<section id="courses">
<div class="section-title reveal"><h2>Cursos A1 – B2</h2></div>
<div class="services">
<div class="service-card reveal"><h3>A1</h3><p>Curso para principiantes desde cero.</p></div>
<div class="service-card reveal"><h3>A2</h3><p>Base sólida con práctica guiada.</p></div>
<div class="service-card reveal"><h3>B1</h3><p>Intermedio con enfoque comunicativo.</p></div>
<div class="service-card reveal"><h3>B2</h3><p>Fluidez avanzada y preparación oficial.</p></div>
</div>
</section>

<!-- DELE -->
<section id="dele">
<div class="section-title reveal"><h2>Preparación DELE</h2></div>
<div class="services">
<div class="service-card reveal"><h3>Formato Oficial</h3><p>Explicación completa del examen DELE.</p></div>
<div class="service-card reveal"><h3>Simulacros</h3><p>Modelos reales y práctica intensiva.</p></div>
<div class="service-card reveal"><h3>Expresión Oral</h3><p>Roleplays y entrenamiento práctico.</p></div>
</div>
</section>

<!-- CONTACT -->
<section id="contact">
<div class="section-title reveal"><h2>Contáctanos</h2></div>
<div class="contact-box reveal">
<p><strong>Ubicación:</strong> Calle Padilla 391, 08025 Barcelona</p>
<p><strong>Horario:</strong> Lunes – Sábado 10:00 AM – 8:00 PM</p>
<p><strong>Email:</strong> panjabidiomas@gmail.com</p>
<p><strong>Teléfono:</strong> +34 612 45 50 57</p>
<br>
<a href="/register" class="btn btn-primary">Inscribirse Ahora</a>
</div>
</section>

<footer>
© 2026 Punjab Idiomas | Todos los derechos reservados
</footer>

<script>
const dot=document.querySelector('.cursor-dot');
const outline=document.querySelector('.cursor-outline');
window.addEventListener('mousemove',e=>{
dot.style.left=e.clientX+'px';
dot.style.top=e.clientY+'px';
outline.style.left=e.clientX+'px';
outline.style.top=e.clientY+'px';
});

function reveal(){
document.querySelectorAll('.reveal').forEach(el=>{
if(el.getBoundingClientRect().top<window.innerHeight-100){
el.classList.add('active');
}
});
}
window.addEventListener('scroll',reveal);
</script>

</body>
</html>