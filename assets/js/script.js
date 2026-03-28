document.addEventListener("DOMContentLoaded", () => {
    
    // --- ZONA DE CONTROL PARA TU MAMÁ ---
    // Si vende la 2, la 5 y la 14, solo tiene que poner los números aquí adentro:
    const joyasVendidas = []; 
    // ------------------------------------

    const maxFotosPosibles = 200; 
    const contenedor = document.getElementById('contenedor-galeria');
    let carritoDeCompras = []; 
    const carritoFlotante = document.getElementById('carrito-flotante');
    const contadorCarrito = document.getElementById('contador-carrito');
    const btnWhatsapp = document.getElementById('btn-whatsapp');
    const numeroTelefono = "527713908178"; 

    for (let i = 1; i <= maxFotosPosibles; i++) {
        // Ahora creamos una "caja" para meter la foto y el letrero juntos
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
                cajaJoya.style.display = 'none'; // Ocultamos toda la caja si no hay foto      
            }
        };

        // VERIFICAMOS SI ESTÁ VENDIDA
        const esVendida = joyasVendidas.includes(i);

        if (esVendida) {
            // Si está vendida, le ponemos el estilo gris
            imagen.classList.add('agotada');
            
            // Y creamos el letrero encima
            let letrero = document.createElement('div');
            letrero.className = 'letrero-vendida';
            letrero.innerText = 'VENDIDA';
            
            cajaJoya.appendChild(imagen);
            cajaJoya.appendChild(letrero);
        } else {
            // Si NO está vendida, la agregamos normal y le damos el evento de clic
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

    btnWhatsapp.addEventListener('click', () => {
        carritoDeCompras.sort((a, b) => a - b);
        let mensajeNormal = `¡Hola! Me encantó tu catálogo. Me interesan las siguientes joyas:\n\n`;
        carritoDeCompras.forEach(numero => {
            mensajeNormal += `- *Joya #${numero}*\n`;
        });
        mensajeNormal += `\n¿Me podrías dar información y precios por favor?`;

        const mensajeCodificado = encodeURIComponent(mensajeNormal);
        const linkWhatsapp = `https://wa.me/${numeroTelefono}?text=${mensajeCodificado}`;
        window.open(linkWhatsapp, '_blank');
    });
});