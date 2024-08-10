var currentPage = 1; // Página actual
var recordsPerPage = 4; // Registros por página

// Función para mostrar los registros de la página actual
function showRecords() {
    var startIndex = (currentPage - 1) * recordsPerPage; // Índice de inicio de los registros en la página actual
    var endIndex = startIndex + recordsPerPage; // Índice de fin de los registros en la página actual

    var tableBody = document.querySelector('tbody');
    tableBody.innerHTML = ''; // Limpiar el cuerpo de la tabla

    for (var i = startIndex; i < endIndex && i < rows.length; i++) {
        var row = rows[i];
        var rowHTML = '<tr>';
        // Mostrar diferentes iconos según el tipo de PIN
        if (row["lockType"] == "Temporal") {
            rowHTML += '<td><ion-icon class="icon-table" name="timer-outline"></ion-icon></td>';
        } else {
            rowHTML += '<td><ion-icon class="icon-table" name="key-outline"></ion-icon></td>';
        }
        rowHTML += '<td>' + row["lockType"] + '</td>';
        rowHTML += '<td>' + row["AccessTime"] + '</td>';
        rowHTML += '<td>' + row["AccessResult"] + '</td>';
        rowHTML += '</tr>';
        tableBody.innerHTML += rowHTML; // Agregar la fila a la tabla
    }
}

// Función para ir a la siguiente página
function nextPage() {
    if (currentPage < Math.ceil(totalRecords / recordsPerPage)) {
        currentPage++;
        showRecords();
    }
}

// Función para ir a la página anterior
function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        showRecords();
    }
}

// Mostrar la primera página al cargar la página
showRecords();
