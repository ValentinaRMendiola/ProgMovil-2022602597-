fetch("http://192.168.1.99:5001/guardar_libro.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json"
  },
  body: JSON.stringify({
    id: "0123456789abcdef0123456789abcdef",
    titulo: "Ejemplo",
    autor: "Autor X",
    descripcion: "Prueba",
    fecha_publicacion: "2025-06-07",
    fecha_modificacion: "2025-06-07 12:00:00",
    eliminado: 0,
    url_imagen: "http://example.com/img.jpg",
    url_pdf: "http://example.com/file.pdf"
  })
})
.then(res => res.json())
.then(data => console.log(data))
.catch(e => console.error(e));
