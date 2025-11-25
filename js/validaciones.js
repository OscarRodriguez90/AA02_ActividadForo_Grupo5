window.onload = () => {
    const campos = ["username","nombre","apellidos","email","fecha_nacimiento","genero","password","confirmar_password"];
    campos.forEach(id => {
        const e=document.getElementById(id);
        if(e)e.onblur=validar;
    });
    validarTodo();
};

function validar(e){
    const campo = e.target;
    const id = campo.id;
    const val = campo.value.trim();
    let msg = "";

    if(!val){
        switch(id){
            case "username": msg="El nombre de usuario es obligatorio."; break;
            case "nombre": msg="El nombre es obligatorio."; break;
            case "apellidos": msg="Los apellidos son obligatorios."; break;
            case "email": msg="El correo electrónico es obligatorio."; break;
            case "fecha_nacimiento": msg="La fecha de nacimiento es obligatoria."; break;
            case "genero": msg="Debe seleccionar un género."; break;
            case "password": msg="La contraseña es obligatoria."; break;
            case "confirmar_password": msg="Debes confirmar la contraseña."; break;
        }
    }

    if(!msg){
        switch(id){
            case "username":
                if(val.length<3) msg="Debe tener al menos 3 caracteres.";
                else if(val.length>50) msg="No puede superar 50 caracteres.";
                break;
            case "nombre":
            case "apellidos":
                if(val.length<2) msg="Debe tener al menos 2 caracteres.";
                else if(val.length>100) msg="No puede superar 100 caracteres.";
                break;
            case "email":
                if(!val.includes("@") || !val.includes(".")) msg="Correo con formato incorrecto.";
                break;
            case "fecha_nacimiento":
                const p = val.split("-");
                if(p.length!==3) { msg="Formato de fecha incorrecto."; break; }
                const a=parseInt(p[0]), m=parseInt(p[1]), d=parseInt(p[2]);
                if(isNaN(a)||isNaN(m)||isNaN(d)) msg="Fecha no válida.";
                else if(m<1||m>12) msg="Mes debe estar entre 1 y 12.";
                else if(d<1||d>31) msg="Día debe estar entre 1 y 31.";
                else if(new Date(a,m-1,d)>new Date()) msg="No puedes poner fecha futura.";
                break;
            case "genero":
                if(!["hombre","mujer","otro"].includes(val)) msg="Género no válido.";
                break;
            case "password":
                if(val.length<8) msg="Debe tener al menos 8 caracteres.";
                break;
            case "confirmar_password":
                if(val!==document.getElementById("password").value) msg="Las contraseñas no coinciden.";
                break;
        }
    }

    const errorDiv = document.getElementById("error-"+id);
    if(msg){
        campo.classList.add("error-border");
        if(errorDiv) errorDiv.textContent=msg;
    }else{
        campo.classList.remove("error-border");
        if(errorDiv) errorDiv.textContent="";
    }

    validarTodo();
}

function validarTodo(){
    const errores = document.querySelectorAll(".mensaje-error");
    let hay=false;
    errores.forEach(e=>{if(e.textContent.trim()!=="") hay=true;});
    const btn = document.getElementById("btnEnviar");
    if(btn) btn.disabled=hay;
}
