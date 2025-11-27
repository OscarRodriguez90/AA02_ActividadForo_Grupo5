window.onload = function () {

    const formRespuesta = document.querySelector('form[action*="post_answer.php"]');

    if (formRespuesta) {
        formRespuesta.addEventListener('submit', function (e) {
            const contenido = formRespuesta.querySelector('textarea[name="content"]').value;

            if (contenido.trim().length === 0) {
                e.preventDefault();
                alert('⚠️ La respuesta no puede estar vacía.');
                return;
            }

            if (contenido.length > 500) {
                e.preventDefault();
                alert('⚠️ La respuesta no puede superar los 500 caracteres.');
            }
        });
    }

    const formPregunta = document.querySelector('form[action*="publish_question.php"]');

    if (formPregunta) {
        formPregunta.addEventListener('submit', function (e) {
            const titulo = formPregunta.querySelector('input[name="title"]').value;
            const descripcion = formPregunta.querySelector('textarea[name="description"]').value;

            if (titulo.trim() === '' || descripcion.trim() === '') {
                e.preventDefault();
                alert('⚠️ Por favor, completa el título y la descripción.');
            }
        });
    }

};