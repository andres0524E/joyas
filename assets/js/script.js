document.addEventListener("DOMContentLoaded", () => {
    
    // Obtenemos en paralelo las ventas y el nuevo índice de fotos ultra-rápido
    Promise.all([
        fetch('assets/js/datos.json?t=' + new Date().getTime()).then(res => res.json()),
        fetch('assets/js/catalogo.json?t=' + new Date().getTime()).then(res => res.ok ? res.json() : [])
    ])
    .then(([datosServer, catalogoArchivos]) => {
            
            const contenedor = document.getElementById('contenedor-galeria');
            contenedor.innerHTML = ''; // Limpiamos la galería por si acaso
            
            let carritoDeCompras = []; 
            
            // Elementos de la interfaz
            const carritoFlotante = document.getElementById('carrito-flotante');
            const contadorCarrito = document.getElementById('contador-carrito');
            const btnWhatsapp = document.getElementById('btn-whatsapp');
            const btnCerrarCarrito = document.getElementById('cerrar-carrito'); 
            const toggleFiltro = document.getElementById('filtro-disponibles');
            
            const visor = document.getElementById('visor-lightbox');
            const imgVisor = document.getElementById('img-visor');
            const btnCerrarVisor = document.getElementById('cerrar-visor');
            const btnAgregarVisor = document.getElementById('btn-visor-agregar');
            let joyaActualEnVisor = 0;

            // EL NÚMERO DE TU MAMÁ
            const numeroTelefono = "527711395823"; 

            let joyasVendidas = Array.isArray(datosServer) ? datosServer : (datosServer.vendidas || []);
            let joyasOcultas = datosServer.ocultas || [];
            
            // --- NUEVO: Memoria anti-duplicados ---
            let joyasYaRenderizadas = []; 

            catalogoArchivos.forEach(nombreArchivo => {
                let i = parseInt(nombreArchivo.split('.')[0]); 
                
                // Evitamos duplicados y saltamos las ocultas
                if(joyasOcultas.includes(i) || joyasYaRenderizadas.includes(i)) return; 
                joyasYaRenderizadas.push(i);

                let cajaJoya = document.createElement('div');
                cajaJoya.className = 'contenedor-joya';

                let imagen = document.createElement('img');
                imagen.className = 'foto-joya';
                imagen.alt = `Joya número ${i}`;
                imagen.dataset.numero = i; 
                imagen.src = `assets/img/${nombreArchivo}`; 
                
                // --- NUEVO: Carga inteligente nativa (No falla en celulares) ---
                imagen.loading = 'lazy'; 

                const esVendida = joyasVendidas.includes(i);

                if (esVendida) {
                    cajaJoya.classList.add('es-vendida'); 
                    imagen.classList.add('agotada');
                    let letrero = document.createElement('div');
                    letrero.className = 'letrero-vendida';
                    letrero.innerText = 'VENDIDA';
                    cajaJoya.appendChild(imagen);
                    cajaJoya.appendChild(letrero);
                } else {
                    cajaJoya.classList.add('es-disponible'); 
                    cajaJoya.appendChild(imagen);
                    
                    imagen.addEventListener('click', function() {
                        const numeroJoya = parseInt(this.dataset.numero);
                        abrirVisor(this.src, numeroJoya);
                    });
                }
                
                contenedor.appendChild(cajaJoya);
            });

            // --- LÓGICA DEL VISOR ---
            function abrirVisor(rutaImg, numero) {
                imgVisor.src = rutaImg;
                joyaActualEnVisor = numero;
                visor.classList.remove('oculto');
                
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