<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Test de restauration MySQL</title>
<link rel="stylesheet" href="style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
<div id="container">
 <h3><marquee><b>Test de restauration des bases MySQL</b></marquee></h3>

<label>Base de données SOURCE</label><br>
<input type="text" id="db_source" placeholder="ex : testrestau">

<br><br>

<label>Tables disponibles</label><br>
<select id="tables" multiple size="6" style="width:300px;"></select>

<br><br>

<label>Base de données RESTORED</label><br>
<input type="text" id="db_restored" placeholder="ex : testrestau_restore">

<hr>

<button id="btnSource" disabled>Valider</button>
<button id="btnRestored" disabled>Restaurer</button>
<button id="btnCompare" disabled>Comparer</button>

<hr>

<h4>Résultat SOURCE</h4>
<div id="result_source"></div>

<h4>Résultat RESTORED</h4>
<div id="result_restored"></div>

<h4>Résultat COMPARAISON</h4>
<div id="result_compare"></div>

<script>
function loadTables() {
    let db = $("#db_source").val();
    if (db.length < 2) return;

    $.post("actions.php", {
        action: "tables",
        db: db
    }, function (data) {
        $("#tables").html(data);
        $("#btnSource, #btnRestored").prop("disabled", false);
    });
}

$("#db_source").on("blur", loadTables);

$("#btnSource").click(function () {
    $.post("actions.php", {
        action: "source",
        db: $("#db_source").val(),
        tables: $("#tables").val()
    }, function (data) {
        $("#result_source").html(data);
        $("#btnCompare").prop("disabled", false);
    });
});

$("#btnRestored").click(function () {

    let dbRestored = $("#db_restored").val().trim();

    if (dbRestored === "") {
        alert("Veuillez saisir le nom de la base RESTORED");
        return;
    }

    let selectedTables = $("#tables").val();
    if (!selectedTables || selectedTables.length === 0) {
        alert("Veuillez sélectionner au moins une table");
        return;
    }

    $.post("actions.php", {
        action: "restored",
        db: dbRestored,
        tables: selectedTables
    }, function (data) {
        $("#result_restored").html(data);
    });
});


$("#btnCompare").click(function () {

    let dbSource = $("#db_source").val().trim();
    if (dbSource === "") {
        alert("Veuillez saisir la base SOURCE avant de comparer");
        return;
    }

    $.post("actions.php", {
        action: "compare",
        db_source: dbSource   // <<< C’est ici la clé
    }, function (data) {
        $("#result_compare").html(data);
    });
});

</script>
</div>
</body>
</html>
