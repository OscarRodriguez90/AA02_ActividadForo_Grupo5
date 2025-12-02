window.onload = () => {
    const campos = ["username", "nombre", "apellidos", "email", "fecha_nacimiento", "genero", "password", "confirmar_password"];
    campos.forEach(id => {
        const e = document.getElementById(id);
        if (e) e.onblur = validar;
    });
    validarTodo();
};

function validar(e) {
    const campo = e.target;
    const id = campo.id;
    const val = campo.value.trim();
    let msg = "";

    if (!val) {
        switch (id) {
            case "username": msg = "El nombre de usuario es obligatorio."; break;
            case "nombre": msg = "El nombre es obligatorio."; break;
            case "apellidos": msg = "Los apellidos son obligatorios."; break;
            case "email": msg = "El correo electrónico es obligatorio."; break;
            case "fecha_nacimiento": msg = "La fecha de nacimiento es obligatoria."; break;
            case "genero": msg = "Debe seleccionar un género."; break;
            case "password": msg = "La contraseña es obligatoria."; break;
            case "confirmar_password": msg = "Debes confirmar la contraseña."; break;
        }
    }

    if (!msg) {
        switch (id) {
            case "username":
                if (val.length < 3) msg = "Debe tener al menos 3 caracteres.";
                else if (val.length > 50) msg = "No puede superar 50 caracteres.";
                break;
            case "nombre":
                if (val.length < 2) msg = "Debe tener al menos 2 caracteres.";
                else if (val.length > 100) msg = "No puede superar 100 caracteres.";
                else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(val)) msg = "El nombre solo puede contener letras.";
                break;
            case "apellidos":
                if (val.length < 2) msg = "Debe tener al menos 2 caracteres.";
                else if (val.length > 100) msg = "No puede superar 100 caracteres.";
                else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(val)) msg = "Los apellidos solo pueden contener letras.";
                break;
            case "email":
                if (!val.includes("@") || !val.includes(".")) msg = "Correo con formato incorrecto.";
                break;
            case "fecha_nacimiento":
                const p = val.split("-");
                if (p.length !== 3) { msg = "Formato de fecha incorrecto."; break; }
                const a = parseInt(p[0]), m = parseInt(p[1]), d = parseInt(p[2]);
                if (isNaN(a) || isNaN(m) || isNaN(d)) msg = "Fecha no válida.";
                else if (m < 1 || m > 12) msg = "Mes debe estar entre 1 y 12.";
                else if (d < 1 || d > 31) msg = "Día debe estar entre 1 y 31.";
                else if (new Date(a, m - 1, d) > new Date()) msg = "No puedes poner fecha futura.";
                break;
            case "genero":
                if (!["hombre", "mujer", "otro"].includes(val)) msg = "Género no válido.";
                break;
            case "password":
                if (val.length < 8) msg = "Debe tener al menos 8 caracteres.";
                break;
            case "confirmar_password":
                if (val !== document.getElementById("password").value) msg = "Las contraseñas no coinciden.";
                break;
        }
    }

    const errorDiv = document.getElementById("error-" + id);
    if (msg) {
        campo.classList.add("error-border");
        if (errorDiv) errorDiv.textContent = msg;
    } else {
        campo.classList.remove("error-border");
        if (errorDiv) errorDiv.textContent = "";
    }

    validarTodo();
}

function validarTodo() {
    const errores = document.querySelectorAll(".mensaje-error");
    let hay = false;
    errores.forEach(e => { if (e.textContent.trim() !== "") hay = true; });
    const btn = document.getElementById("btnEnviar");
    if (btn) btn.disabled = hay;
}

// Validaciones para editar_perfil.php
function validarEditarPerfil(e) {
    const campo = e.target;
    const id = campo.id;
    const val = campo.value.trim();
    let msg = "";

    // Validar que ningún campo esté vacío
    if (!val) {
        switch (id) {
            case "nombre": msg = "El nombre es obligatorio."; break;
            case "apellidos": msg = "Los apellidos son obligatorios."; break;
            case "email": msg = "El correo electrónico es obligatorio."; break;
            case "fecha_nacimiento": msg = "La fecha de nacimiento es obligatoria."; break;
            case "genero": msg = "Debe seleccionar un género."; break;
        }
    }

    // Validaciones específicas por campo
    if (!msg) {
        switch (id) {
            case "nombre":
                if (val.length < 2) msg = "El nombre debe tener al menos 2 caracteres.";
                else if (val.length > 50) msg = "El nombre no puede superar los 50 caracteres.";
                else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(val)) msg = "El nombre solo puede contener letras.";
                break;

            case "apellidos":
                if (val.length < 2) msg = "Los apellidos deben tener al menos 2 caracteres.";
                else if (val.length > 50) msg = "Los apellidos no pueden superar los 50 caracteres.";
                else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(val)) msg = "Los apellidos solo pueden contener letras.";
                break;

            case "email":
                // Validar formato de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(val)) {
                    msg = "El formato del correo electrónico no es válido.";
                }
                break;

            case "fecha_nacimiento":
                // Validar formato de fecha
                const partes = val.split("-");
                if (partes.length !== 3) {
                    msg = "El formato de la fecha no es correcto.";
                    break;
                }

                const ano = parseInt(partes[0]);
                const mes = parseInt(partes[1]);
                const dia = parseInt(partes[2]);

                if (isNaN(ano) || isNaN(mes) || isNaN(dia)) {
                    msg = "La fecha no es válida.";
                } else if (mes < 1 || mes > 12) {
                    msg = "El mes debe estar entre 1 y 12.";
                } else if (dia < 1 || dia > 31) {
                    msg = "El día debe estar entre 1 y 31.";
                } else {
                    // Validar que no sea una fecha futura
                    const fechaIngresada = new Date(ano, mes - 1, dia);
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);

                    if (fechaIngresada > hoy) {
                        msg = "No se pueden introducir fechas futuras.";
                    }
                }
                break;

            case "genero":
                // Validar que se ha seleccionado un género válido
                if (!["hombre", "mujer", "otro"].includes(val)) {
                    msg = "El género seleccionado no es válido.";
                }
                break;
        }
    }

    // Mostrar u ocultar mensaje de error
    const errorDiv = document.getElementById("error-" + id);
    if (msg) {
        campo.classList.add("error-border");
        if (errorDiv) errorDiv.textContent = msg;
    } else {
        campo.classList.remove("error-border");
        if (errorDiv) errorDiv.textContent = "";
    }

    validarTodoEditarPerfil();
}

function validarTodoEditarPerfil() {
    const errores = document.querySelectorAll(".mensaje-error");
    let hayErrores = false;
    errores.forEach(e => {
        if (e.textContent.trim() !== "") hayErrores = true;
    });
    const btnGuardar = document.querySelector("button[type='submit']");
    if (btnGuardar) btnGuardar.disabled = hayErrores;
}

// Inicializar validaciones para editar_perfil.php
function iniciarValidacionesEditarPerfil() {
    const camposEditarPerfil = ["nombre", "apellidos", "email", "fecha_nacimiento", "genero"];
    camposEditarPerfil.forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.onblur = validarEditarPerfil;
        }
    });
    validarTodoEditarPerfil();
}
