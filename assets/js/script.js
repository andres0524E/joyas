document.addEventListener("DOMContentLoaded", () => {
    
    // Leemos la BD de Hostinger
    fetch('assets/js/datos.json?t=' + new Date().getTime())
        .then(respuesta => respuesta.json())
        .then(joyasVendidas => {
            
            const maxFotosPosibles = 200; 
            const contenedor = document.getElementById('contenedor-galeria');
            let carritoDeCompras = []; 
            const carritoFlotante = document.getElementById('carrito-flotante');
            const contadorCarrito = document.getElementById('contador-carrito');
            const btnWhatsapp = document.getElementById('btn-whatsapp');
            const btnCerrarCarrito = document.getElementById('cerrar-carrito'); // El nuevo botón X
            
            // EL NUEVO NÚMERO DE TU MAMÁ
            const numeroTelefono = "527711395823"; 

            for (let i = 1; i <= maxFotosPosibles; i++) {
                let cajaJoya = document.createElement('div');
                cajaJoya.className = 'contenedor-joya';

                let imagen = document.createElement('img');
                imagen.className = 'foto-joya';
                imagen.alt = `Joya número ${i}`;
                imagen.dataset.numero = i; 
                imagen.src = `assets/img/${i}.jpg`; 
                
                imagen.onerror = function() {
                    if (this.src.endsWith('.jpg')) {
                        this.src = `assets/img/${i}.jpeg`;
                    } else if (this.src.endsWith('.jpeg')) {
                        this.src = `assets/img/${i}.png`; 
                    } else {
                        cajaJoya.style.display = 'none';      
                    }
                };

                const esVendida = joyasVendidas.includes(i);

                if (esVendida) {
                    imagen.classList.add('agotada');
                    let letrero = document.createElement('div');
                    letrero.className = 'letrero-vendida';
                    letrero.innerText = 'VENDIDA';
                    cajaJoya.appendChild(imagen);
                    cajaJoya.appendChild(letrero);
                } else {
                    cajaJoya.appendChild(imagen);
                    imagen.addEventListener('click', function() {
                        const numeroJoya = parseInt(this.dataset.numero);
                        if (carritoDeCompras.includes(numeroJoya)) {
                            carritoDeCompras = carritoDeCompras.filter(item => item !== numeroJoya);
                            this.classList.remove('seleccionada'); 
                        } else {
                            carritoDeCompras.push(numeroJoya);
                            this.classList.add('seleccionada'); 
                        }
                        actualizarVistaCarrito();
                    });
                }
                contenedor.appendChild(cajaJoya);
            }

            function actualizarVistaCarrito() {
                contadorCarrito.innerText = carritoDeCompras.length;
                if (carritoDeCompras.length > 0) {
                    carritoFlotante.classList.remove('oculto');
                } else {
                    carritoFlotante.classList.add('oculto');
                }
            }

            // --- LÓGICA PARA EL BOTÓN "X" ---
            btnCerrarCarrito.addEventListener('click', () => {
                carritoDeCompras = []; // Vaciamos la lista de compras
                // Le quitamos el marco dorado a todas las fotos seleccionadas
                document.querySelectorAll('.foto-joya.seleccionada').forEach(img => {
                    img.classList.remove('seleccionada'); 
                });
                actualizarVistaCarrito(); // Ocultamos la barra flotante
            });

            // --- LÓGICA PARA EL BOTÓN WHATSAPP ---
            btnWhatsapp.addEventListener('click', () => {
                carritoDeCompras.sort((a, b) => a - b);
                
                // EL NUEVO MENSAJE CON EMOJIS
                let mensajeNormal = `¡Hola! Me encantó el nuevo catálogo. Y quiero las siguientes joyas 💍:\n\n`;
                carritoDeCompras.forEach(numero => {
                    mensajeNormal += `- *Joya #${numero}*\n`;
                });
                mensajeNormal += `\n¿Me podrías dar información y precios por favor? 💖`;

                const mensajeCodificado = encodeURIComponent(mensajeNormal);
                const linkWhatsapp = `https://wa.me/${numeroTelefono}?text=${mensajeCodificado}`;
                window.open(linkWhatsapp, '_blank');
            });
            
        })
        .catch(error => console.error("Error cargando los datos:", error));
});