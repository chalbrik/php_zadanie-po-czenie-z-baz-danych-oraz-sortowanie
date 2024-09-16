<?php

// contracts

//0 => id, 2 => nazwa przedsiebiorcy, 4 => NIP, 10 => kwota,

$mysqli = new mysqli("host", "user", "password", "database");

$x = "id = ? AND kwota > 10; ";

if ($_GET['akcja'] == 5) {

    // show contracts with amount more than 10

    switch ($_GET['sort']) {

        case 1:
            $sql_orderby = " order by nazwa_przedsiebiorcy, NIP";
            break;

        case 2:
            $sql_orderby = " order by kwota";
            break;
    }

    ($sql_orderby == ' order by nazwa_przedsiebiorcy, NIP') ? $b = 'DESC' : $b = 'ASC';

    $i = $mysqli->prepare("SELECT * FROM contracts WHERE $x ORDER BY $sql_orderby $b");
    $i->bind_param("i", $_GET['i']);
    $i->execute();

    $a = $i->get_result();


    echo "<html>";
    echo "<body style='background-color: $dg_bgcolor'>";

    echo "<br>";

    echo "<table width=95%>";

    while ($z = $a->fetch_assoc()) {

        echo '<tr>';

        echo '<td>' . $z[0] . '</td>';

        echo '<td>';

        echo $z[2];

        if ($z[10] > 10) {
            echo ' ' . $z[10];
        }
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';
} else {

    $c = $mysqli->prepare("SELECT * FROM contracts WHERE $x ORDER BY id");
    $c->bind_param('i', $_GET['i']);
    $c->execute();

    $d = $c->get_result();

    echo "<html>";
    echo "<body style='background-color: $dg_bgcolor'>";

    echo "<br>";

    echo "<table width=95%>";

    while ($z = $d->fetch_assoc()) {

        echo '<tr>';
        echo '<td>' . $z[0] . '</td>';
        echo '<td>' . $z[2] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';
}

$mysqli->close();
