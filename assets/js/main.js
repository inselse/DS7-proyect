document.addEventListener('DOMContentLoaded', function () {

    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('sidebar-expandida');
        });

        document.addEventListener('click', function (e) {
            const isMobile = window.innerWidth <= 1024;
            if (isMobile && sidebar.classList.contains('sidebar-expandida')) {
                const isSidebarClick = sidebar.contains(e.target);
                const isToggleClick = toggleBtn.contains(e.target);
                if (!isSidebarClick && !isToggleClick) {
                    sidebar.classList.remove('sidebar-expandida');
                }
            }
        });
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let valido = true;

            if (!email.value.trim()) {
                marcarInvalido(email, 'Ingrese su correo electronico');
                valido = false;
            } else {
                marcarValido(email);
            }

            if (!password.value.trim()) {
                marcarInvalido(password, 'Ingrese su contrasena');
                valido = false;
            } else {
                marcarValido(password);
            }

            if (!valido) {
                e.preventDefault();
            }
        });
    }

    function marcarInvalido(input, mensaje) {
        input.style.borderColor = '#EF4444';
        input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.12)';

        const existente = input.parentElement.querySelector('.error-campo');
        if (!existente) {
            const error = document.createElement('span');
            error.className = 'error-campo';
            error.style.cssText = 'font-size:0.75rem;color:#EF4444;margin-top:0.25rem;';
            error.textContent = mensaje;
            input.parentElement.appendChild(error);
        }
    }

    function marcarValido(input) {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        const error = input.parentElement.querySelector('.error-campo');
        if (error) {
            error.remove();
        }
    }

    const busquedaInput = document.getElementById('buscarVehiculo');
    if (busquedaInput) {
        let timeout = null;
        busquedaInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                const termino = busquedaInput.value.trim();
                if (termino.length >= 2 || termino.length === 0) {
                    const url = new URL(window.location.href);
                    if (termino) {
                        url.searchParams.set('buscar', termino);
                    } else {
                        url.searchParams.delete('buscar');
                    }
                    url.searchParams.delete('pagina');
                    window.location.href = url.toString();
                }
            }, 400);
        });
    }

    const primerInput = document.querySelector('.campo input, .campo select, .campo textarea');
    if (primerInput && !primerInput.value) {
        primerInput.focus();
    }

    document.querySelectorAll('.btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var rect = btn.getBoundingClientRect();
            var ripple = document.createElement('span');
            ripple.className = 'ripple';
            var size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            btn.appendChild(ripple);
            setTimeout(function () { ripple.remove(); }, 600);
        });
    });

    window.toast = function (mensaje, tipo) {
        tipo = tipo || 'info';
        var container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;max-width:360px;';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + tipo;
        var iconos = { exito: 'check-circle', error: 'exclamation-circle', info: 'info-circle', advertencia: 'exclamation-triangle' };
        toast.innerHTML = '<i class="fas fa-' + (iconos[tipo] || 'info-circle') + '"></i><span>' + mensaje + '</span>';
        container.appendChild(toast);
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(function () { toast.remove(); }, 300);
        }, 4000);
    };

    var contenidoInterno = document.querySelector('.contenido-interno');
    if (contenidoInterno) {
        contenidoInterno.addEventListener('scroll', function () {
            var header = this.closest('.contenido-principal').querySelector('.header-superior');
            if (header) {
                if (this.scrollTop > 8) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }
            }
        });
    }
});
