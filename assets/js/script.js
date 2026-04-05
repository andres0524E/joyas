document.addEventListener("DOMContentLoaded", () => {
    fetch('assets/js/datos.json?t=' + new Date().getTime())
        .then(respuesta => respuesta.json())
        .then(datosServer => {
            
            const maxFotosPosibles = 200; 
            const contenedor = document.getElementById('contenedor-galeria');
            let carritoDeCompras = []; 
            
            // Elementos del UI
            const carritoFlotante = document.getElementById('carrito-flotante');
            const contadorCarrito = document.getElementById('contador-carrito');
            const btnWhatsapp = document.getElementById('btn-whatsapp');
            const btnCerrarCarrito = document.getElementById('cerrar-carrito'); 
            const toggleFiltro = document.getElementById('filtro-disponibles');
            
            // Elementos del Visor
            const visor = document.getElementById('visor-lightbox');
            const imgVisor = document.getElementById('img-visor');
            const btnCerrarVisor = document.getElementById('cerrar-visor');
            const btnAgregarVisor = document.getElementById('btn-visor-agregar');
            let joyaActualEnVisor = 0;

            const numeroTelefono = "527711395823"; 

            // Datos del Servidor (Ocultas y Vendidas)
            // Si el servidor solo mandó un array simple (versión vieja), lo adaptamos.
            let joyasVendidas = Array.isArray(datosServer) ? datosServer : (datosServer.vendidas || []);
            let joyasOcultas = datosServer.ocultas || [];

            // --- ANIMACIÓN DE ENTRADA (LAZY LOAD SUAVE) ---
            const observadorCarga = new IntersectionObserver((entradas) => {
                entradas.forEach(entrada => {
                    if (entrada.isIntersecting) {
                        entrada.target.classList.add('visible');
                        observadorCarga.unobserve(entrada.target);
                    }
                });
            }, { threshold: 0.1 });

            for (let i = 1; i <= maxFotosPosibles; i++) {
                // Si tu mamá la marcó como oculta, no la dibujamos
                if(joyasOcultas.includes(i)) continue;

                let cajaJoya = document.createElement('div');
                cajaJoya.className = 'contenedor-joya';

                let imagen = document.createElement('img');
                imagen.className = 'foto-joya';
                imagen.alt = `Joya número ${i}`;
                imagen.dataset.numero = i; 
                imagen.src = `assets/img/${i}.jpg`; 
                
                imagen.onerror = function() {
                    if (this.src.endsWith('.jpg')) { this.src = `assets/img/${i}.jpeg`; } 
                    else if (this.src.endsWith('.jpeg')) { this.src = `assets/img/${i}.png`; } 
                    else { cajaJoya.style.display = 'none'; }
                };

                const esVendida = joyasVendidas.includes(i);

                if (esVendida) {
                    cajaJoya.classList.add('es-vendida'); // Para el filtro
                    imagen.classList.add('agotada');
                    let letrero = document.createElement('div');
                    letrero.className = 'letrero-vendida';
                    letrero.innerText = 'VENDIDA';
                    cajaJoya.appendChild(imagen);
                    cajaJoya.appendChild(letrero);
                } else {
                    cajaJoya.classList.add('es-disponible'); // Para el filtro
                    cajaJoya.appendChild(imagen);
                    
                    // --- NUEVA LÓGICA: ABRIR VISOR AL TOCAR FOTO ---
                    imagen.addEventListener('click', function() {
                        const numeroJoya = parseInt(this.dataset.numero);
                        abrirVisor(this.src, numeroJoya);
                    });
                }
                
                contenedor.appendChild(cajaJoya);
                observadorCarga.observe(cajaJoya); // Animación
            }

            // --- LÓGICA DEL VISOR ---
            function abrirVisor(rutaImg, numero) {
                imgVisor.src = rutaImg;
                joyaActualEnVisor = numero;
                visor.classList.remove('oculto');
                
                // Checar si ya estaba en el carrito
                if (carritoDeCompras.includes(numero)) {
                    btnAgregarVisor.innerText = "Quitar de la selección";
                    btnAgregarVisor.classList.add('ya-agregado');
                } else {
                    btnAgregarVisor.innerText = "Seleccionar esta joya";
                    btnAgregarVisor.classList.remove('ya-agregado');
                }
            }

            btnCerrarVisor.addEventListener('click', () => { visor.classList.add('oculto'); });
            visor.addEventListener('click', (e) => { if(e.target === visor) visor.classList.add('oculto'); });

            // Botón de agregar dentro del visor
            btnAgregarVisor.addEventListener('click', () => {
                const num = joyaActualEnVisor;
                const imgEnGaleria = document.querySelector(`img[data-numero='${num}']`);

                if (carritoDeCompras.includes(num)) {
                    carritoDeCompras = carritoDeCompras.filter(item => item !== num);
                    if(imgEnGaleria) imgEnGaleria.classList.remove('seleccionada');
                    btnAgregarVisor.innerText = "Seleccionar esta joya";
                    btnAgregarVisor.classList.remove('ya-agregado');
                } else {
                    carritoDeCompras.push(num);
                    if(imgEnGaleria) imgEnGaleria.classList.add('seleccionada');
                    btnAgregarVisor.innerText = "Quitar de la selección";
                    btnAgregarVisor.classList.add('ya-agregado');
                }
                actualizarVistaCarrito();
            });

            // --- LÓGICA DEL FILTRO ---
            toggleFiltro.addEventListener('change', function() {
                const todasLasVendidas = document.querySelectorAll('.es-vendida');
                if (this.checked) {
                    todasLasVendidas.forEach(caja => caja.style.display = 'none');
                } else {
                    todasLasVendidas.forEach(caja => caja.style.display = 'block');
                }
            });

            // --- CARRITO Y WHATSAPP ---
            function actualizarVistaCarrito() {
                contadorCarrito.innerText = carritoDeCompras.length;
                if (carritoDeCompras.length > 0) { carritoFlotante.classList.remove('oculto'); } 
                else { carritoFlotante.classList.add('oculto'); }
            }

            btnCerrarCarrito.addEventListener('click', () => {
                carritoDeCompras = []; 
                document.querySelectorAll('.foto-joya.seleccionada').forEach(img => img.classList.remove('seleccionada'));
                actualizarVistaCarrito(); 
            });

            btnWhatsapp.addEventListener('click', () => {
                carritoDeCompras.sort((a, b) => a - b);
                let mensajeNormal = `¡Hola! Me encantó el nuevo catálogo. Y quiero las siguientes joyas:\n\n`;
                carritoDeCompras.forEach(numero => { mensajeNormal += `- *Joya #${numero}*\n`; });
                mensajeNormal += `\n¿Me podrías dar información y precios por favor?`;

                const linkWhatsapp = `https://wa.me/${numeroTelefono}?text=${encodeURIComponent(mensajeNormal)}`;
                window.open(linkWhatsapp, '_blank');
            });
            
        }).catch(error => console.error("Error cargando los datos:", error));
});