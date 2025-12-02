# TBForo: nuestro rincón de dudas y soluciones

TBForo es un proyecto que nace en clase, pero está pensado para cualquiera que disfrute aprendiendo en comunidad. Imaginamos un espacio sencillo donde estudiantes y curiosos puedan plantear preguntas, compartir respuestas breves y acompañarlas con capturas o fragmentos de código.

## ¿Qué puedes hacer dentro?
- **Crear tu perfil** en minutos: registro con nombre, usuario y contraseña.
- **Publicar preguntas claras** con un título y una descripción para que otros entiendan tu reto.
- **Responder con estilo**: cada respuesta permite un texto corto (máx. 500 caracteres) y varios adjuntos.
- **Dar "likes"** cuando una pregunta o respuesta te resulte útil.
- **Buscar gente afín**: encuentra compañeros por nombre o usuario y envíales solicitudes de amistad.
- **Chatear en privado** con tus contactos aceptados para profundizar en una idea.

## Recorrido típico
1. Te registras e inicias sesión.
2. Visitas la portada (`index.php`) y hojeas las preguntas recientes o usas el buscador.
3. Publicas tu propia duda desde "Nueva pregunta" o respondes a otra persona.
4. Gestionas tus amistades en `friends.php` para crear un pequeño círculo de confianza y poder hablar por el chat integrado.
5. Revisas tu perfil para ver estadísticas rápidas: cuántas preguntas hiciste, cuántas respuestas diste y cuántos likes has repartido.

## ¿Cómo lo probamos en clase?
- Trabajamos con un servidor local tipo WAMP/XAMPP y una base de datos MySQL.
- Guardamos los archivos en la carpeta `AA02_ActividadForo_Grupo5` y apuntamos el navegador a `http://localhost/AA02_ActividadForo_Grupo5`.
- Importamos el esquema de la base de datos y creamos un usuario para empezar.

Si quieres revisarlo por dentro, encontrarás las páginas principales en la raíz (como `index.php`, `pregunta.php`, `chat.php`) y las acciones de guardado dentro de la carpeta `actions/`. Aun así, nuestro objetivo con este README es que cualquier persona ajena al código entienda que TBForo es un espacio colaborativo, pensado para aprender compartiendo.

Proyecto académico desarrollado en el módulo DAW2 para recrear un foro técnico inspirado en StackOverflow. Los usuarios pueden registrarse, iniciar sesión, formular preguntas, responder, adjuntar archivos y relacionarse mediante solicitudes de amistad, chat privado y sistema de likes.

## Características principales
- **Autenticación completa**: Registro con validaciones en PHP/JS, contraseñas cifradas con `password_hash()` (BCRYPT) y login por usuario o email.
- **Gestión de preguntas**: Crear, editar y eliminar preguntas propias desde `crear_pregunta.php`, `editar_pregunta.php` y acciones en `actions/`.
- **Respuestas con adjuntos**: Formular respuestas (máx. 500 caracteres) y anexar múltiples archivos almacenados en `uploads/` y registrados en `tbl_archivos`.
- **Likes en publicaciones**: Botón toggle (`actions/like.php`) que evita likes duplicados y muestra contador en preguntas y respuestas.
- **Búsqueda**: Filtro por título en `index.php` y buscador de usuarios integrado en `friends.php`.
- **Red social**: Solicitudes de amistad, listados de amigos, indicador de estado y chat privado (`chat.php`) exclusivo entre contactos aceptados.


## Estructura relevante
```
AA02_ActividadForo_Grupo5/
├── actions/          # Controladores para likes, CRUD de preguntas y respuestas
├── assets/           # CSS y JS (validaciones en assets/js/validaciones.js)
├── config/           # `conexion.php` con la conexión PDO
├── view/             # Formularios de login/register/logout
├── chat.php          # Chat privado entre amigos
├── friends.php       # Gestión de solicitudes y búsqueda de usuarios
├── index.php         # Listado de preguntas con buscador y likes
├── pregunta.php      # Vista detallada con respuestas y adjuntos
└── uploads/          # Almacenamiento físico de archivos subidos
```

## Buenas prácticas aplicadas
- Conexión PDO con `ERRMODE_EXCEPTION` y `prepare()` en todas las consultas para mitigar SQLi.
- Validaciones HTML/JS y sanitización con `filter_var`, `preg_match` y reglas de negocio en `proc/validaciones.php`.
- Separación de responsabilidades: vistas limpias y lógica de inserción/actualización dentro de `actions/`.
- Control de sesión centralizado: cada página restringida verifica `$_SESSION['user_id']` y redirige a `view/login.php` si no está autenticado.