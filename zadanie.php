<?php

class Database
{
    private PDO $db;
    private $setFetchType = PDO::FETCH_ASSOC;

    public function __construct()
    {
        $config = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'zadanie_rekrutacyjne'
        ];

        try {
            $this->db = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']};charset=utf8",
                $config['user'],
                $config['password'],
                [
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );
            echo "Połączenie z bazą danych zostało pomyślnie nawiązane<br>";
        } catch (PDOException $error) {
            exit('Błąd połączenia z bazą danych: ' . $error->getMessage());
        }
    }

    public function executeQuery(string $query, array $params = [], bool $fetchType = true)
    {
        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();

        $fetchType ? $this->setFetchType = PDO::FETCH_ASSOC : $this->setFetchType = PDO::FETCH_NUM;

        return $stmt->fetchAll($this->setFetchType);
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }
}

class DataOrganiser
{

    public function mapSimpleNumericArray(array $databaseData)
    {
        $mappedData = [];

        foreach ($databaseData as $data) {
            $key = $data[0];
            $value = $data[1];
            $mappedData[$key] = $value;
        }

        return $mappedData;
    }

    public function checkPayments($invoices, $payments)
    {
        $results = [];

        foreach ($invoices as $id => $invoiceAmount) {
            if (isset($payments[$id])) {
                $difference = $payments[$id] - $invoiceAmount;
                if ($difference != 0) {
                    $results[$id] = $difference;
                }
            } else {
                $results[$id] = "Brak wpłaty";
            }
        }
        return $results;
    }

    public function splitArray($inputArray)
    {
        $exessPayment = [];
        $underPayment = [];

        foreach ($inputArray as $id => $value) {
            if ($value === "Brak wpłaty") {
                $underPayment[$id] = $value;
            } elseif ($value > 0) {
                $exessPayment[$id] = $value;
            } else {
                $underPayment[$id] = $value;
            }
        }

        return [
            'nadpłaty' => $exessPayment,
            'niedopłaty' => $underPayment
        ];
    }

    public function setOrderByPayments($sortOption)
    {
        $orderBy = 'ORDER BY faktury.numer ASC';

        switch ($sortOption) {
            case 'numer_asc':
                $orderBy = 'ORDER BY faktury.numer ASC';
                break;
            case 'numer_desc':
                $orderBy = 'ORDER BY faktury.numer DESC';
                break;
            case 'nazwa_asc':
                $orderBy = 'ORDER BY klienci.nazwa_przedsiebiorcy ASC';
                break;
            case 'nazwa_desc':
                $orderBy = 'ORDER BY klienci.nazwa_przedsiebiorcy DESC';
                break;
        }

        return $orderBy;
    }

    public function setOrderByDates($sortOption)
    {
        $orderBy = 'ORDER BY faktury.numer ASC';

        switch ($sortOption) {
            case 'numer_asc':
                $orderBy =  'ORDER BY faktury.numer ASC';
                break;
            case 'numer_desc':
                $orderBy =  'ORDER BY faktury.numer DESC';
                break;
            case 'nazwa_asc':
                $orderBy =  'ORDER BY klienci.nazwa_przedsiebiorcy ASC';
                break;
            case 'nazwa_desc':
                $orderBy =  'ORDER BY klienci.nazwa_przedsiebiorcy DESC';
                break;
            case 'termin_platnosci_asc':
                $orderBy =  'ORDER BY faktury.termin_platnosci ASC';
                break;
            case 'termin_platnosci_desc':
                $orderBy =  'ORDER BY faktury.termin_platnosci DESC';
                break;
            case 'data_wplaty_asc':
                $orderBy =  'ORDER BY platnosci.data_wplaty ASC';
                break;
            case 'data_wplaty_desc':
                $orderBy =  'ORDER BY platnosci.data_wplaty DESC';
                break;
        }

        return $orderBy;
    }

    function compareDates(array $invoiceDates, array $paymentDates)
    {
        $resultKeys = [];

        foreach ($paymentDates as $key => $paymentDate) {
            $paymentDateTime = new DateTime($paymentDate);

            if (isset($invoiceDates[$key])) {
                $invoiceDateTime = new DateTime($invoiceDates[$key]);

                if ($paymentDateTime > $invoiceDateTime) {
                    $resultKeys[] = $key;
                }
            } else {
                $resultKeys[] = $key;
            }
        }

        foreach ($invoiceDates as $key => $invoiceDate) {
            if (!isset($paymentDates[$key])) {
                $resultKeys[] = $key;
            }
        }

        return $resultKeys;
    }
}


$db = new Database();

$dataOrganiser = new DataOrganiser();

//pobierz platnosci z bazy danych

$sqlPlatnosci = "SELECT id_faktury, kwota FROM platnosci ORDER BY id_faktury";
$sqlFaktury = "SELECT id_faktury, suma_brutto FROM faktury ORDER BY id_faktury";

$platnosci = $db->executeQuery($sqlPlatnosci, [], false);
$sumyFaktur = $db->executeQuery($sqlFaktury, [], false);

$platnosci = $dataOrganiser->mapSimpleNumericArray($platnosci);
$sumyFaktur = $dataOrganiser->mapSimpleNumericArray($sumyFaktur);

$sprawdźPłatnosci = $dataOrganiser->checkPayments($sumyFaktur, $platnosci);

$nadpłaty = $dataOrganiser->splitArray($sprawdźPłatnosci)['nadpłaty'];
$niedopłaty = $dataOrganiser->splitArray($sprawdźPłatnosci)['niedopłaty'];

//przygotowanie parametrów do wysłania zapytania do bazy danych

$nadplatySortOpcja = $_GET['nadplaty-sort'] ?? '';
$nadplatySortOpcja = htmlspecialchars($nadplatySortOpcja, ENT_QUOTES, 'UTF-8');
$nadplatySort = $dataOrganiser->setOrderByPayments($nadplatySortOpcja);

$nadpłatyParametry = array_keys($nadpłaty);
$nadpłatyPlaceholders = implode(',', array_fill(0, count($nadpłatyParametry), '?'));



//Zapytanie o dane do wypełnienia tabeli

$raportNadpłatZapytanie = "SELECT faktury.id_faktury, faktury.numer, klienci.nazwa_przedsiebiorcy 
                          FROM faktury 
                          INNER JOIN klienci ON faktury.id_przedsiebiorcy = klienci.id_przedsiebiorcy 
                          WHERE faktury.id_faktury IN ($nadpłatyPlaceholders) $nadplatySort";

// Wykonaj zapytanie
$nadpłatyDane = $db->executeQuery($raportNadpłatZapytanie, $nadpłatyParametry);

$nadpłatyMap = [];
foreach ($nadpłatyDane as $faktura) {
    $nadpłatyMap[$faktura['id_faktury']] = $faktura;
}


$niedoplatySortOpcja = $_GET['niedoplaty-sort'] ?? '';
$niedoplatySortOpcja = htmlspecialchars($niedoplatySortOpcja, ENT_QUOTES, 'UTF-8');
$niedoplatySort = $dataOrganiser->setOrderByPayments($niedoplatySortOpcja);

$niedoplatyParametry = array_keys($niedopłaty);
$niedoplatyPlaceholders = implode(',', array_fill(0, count($niedoplatyParametry), '?'));


$raportNiedopłatZapytanie = "SELECT faktury.id_faktury, faktury.numer, klienci.nazwa_przedsiebiorcy 
                          FROM faktury 
                          INNER JOIN klienci ON faktury.id_przedsiebiorcy = klienci.id_przedsiebiorcy 
                          WHERE faktury.id_faktury IN ($niedoplatyPlaceholders) $niedoplatySort";


$niedopłatyDane = $db->executeQuery($raportNiedopłatZapytanie, $niedoplatyParametry);


$niedopłatyMap = [];
foreach ($niedopłatyDane as $faktura) {
    $niedopłatyMap[$faktura['id_faktury']] = $faktura;
}


//Pobierz daty z bazy danych

$sqlDatyTerminuPlatnosci = "SELECT id_faktury, termin_platnosci FROM faktury ORDER BY id_faktury";
$sqlDatyPlatnosci = "SELECT id_faktury, data_wplaty FROM platnosci ORDER BY id_faktury";


$terminyPlatnosci = $db->executeQuery($sqlDatyTerminuPlatnosci, [], false);
$datyPlatnosci = $db->executeQuery($sqlDatyPlatnosci, [], false);

$terminyPlatnosci = $dataOrganiser->mapSimpleNumericArray($terminyPlatnosci);
$datyPlatnosci = $dataOrganiser->mapSimpleNumericArray($datyPlatnosci);

$nioplaconeFakturyId = $dataOrganiser->compareDates($terminyPlatnosci, $datyPlatnosci);

$nieoplaconeFakturySortOpcja = $_GET['nieoplacone-faktury-sort'] ?? '';
$nieoplaconeFakturySortOpcja = htmlspecialchars($nieoplaconeFakturySortOpcja, ENT_QUOTES, 'UTF-8');

$nieoplaconeFakturySort = $dataOrganiser->setOrderByDates($nieoplaconeFakturySortOpcja);

$parametryNieoplaconeFakturyId = array_values($nioplaconeFakturyId);
$placeholdersNieoplaconeFakturyId = implode(',', array_fill(0, count($parametryNieoplaconeFakturyId), '?'));

$sqlRaportNieoplaconychFaktur = "SELECT 
                                    faktury.numer,
                                    klienci.nazwa_przedsiebiorcy,
                                    faktury.termin_platnosci,
                                    platnosci.data_wplaty
                                FROM 
                                    faktury
                                INNER JOIN 
                                    klienci ON faktury.id_przedsiebiorcy = klienci.id_przedsiebiorcy
                                LEFT JOIN 
                                    platnosci ON faktury.id_faktury = platnosci.id_faktury
                                WHERE 
                                    faktury.id_faktury IN ($placeholdersNieoplaconeFakturyId) $nieoplaconeFakturySort";

$nieoplaconeFakturyDane = $db->executeQuery($sqlRaportNieoplaconychFaktur, $parametryNieoplaconeFakturyId);


// Generowanie raportu nadpłaty na koncie klienta

echo "<br><br>";


echo "<h2 style='margin: 0 0 5px 0;'>Raport nadpłat</h2>";

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr>";
echo "<th style='padding: 8px;'>Nazwa klienta</th>";
echo "<th style='padding: 8px;'>Numer faktury</th>";
echo "<th style='padding: 8px;'>Wartość nadpłaty</th>";
echo "</tr>";

foreach ($nadpłatyMap as $idFaktury => $faktura) {
    if (isset($nadpłaty[$idFaktury]) && $nadpłaty[$idFaktury] !== "Brak wpłaty") {
        $wartoscNadpłaty = $nadpłaty[$idFaktury];
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$faktura['nazwa_przedsiebiorcy']}</td>";
        echo "<td style='padding: 8px;'>{$faktura['numer']}</td>";
        echo "<td style='padding: 8px;'>" . abs($wartoscNadpłaty) . " PLN</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<br><br>";

echo "<form method='GET' action=''>";
echo    "<label for='nadplaty-sort'>Sortuj według:</label>";
echo    "<select name='nadplaty-sort' id='nadplaty-sort'>";
echo        "<option value=''>Wybierz sortowanie</option>";
echo        "<option value='numer_asc'>Sortuj według numeru faktury rosnąco</option>";
echo        "<option value='numer_desc'>Sortuj według numeru faktury malejąco</option>";
echo        "<option value='nazwa_asc'>Sortuj według nazwy klienta rosnąco</option>";
echo        "<option value='nazwa_desc'>Sortuj według nazwy klienta malejąco</option>";
echo    "</select>";
echo    "<input type='submit' value='Sortuj'>";
echo "</form>";

echo "<br><br>";
echo "<br><br>";

// Generowanie raportu niedopłaty na koncie klienta

echo "<h2 style='margin: 0 0 5px 0;'>Raport niedopłat</h2>";

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr>";
echo "<th style='padding: 8px;'>Nazwa klienta</th>";
echo "<th style='padding: 8px;'>Numer faktury</th>";
echo "<th style='padding: 8px;'>Wartość niedopłaty</th>";
echo "</tr>";

foreach ($niedopłatyMap as $idFaktury => $faktura) {
    if (isset($niedopłaty[$idFaktury]) && $niedopłaty[$idFaktury] !== "Brak wpłaty") {
        $wartoscNiedopłaty = $niedopłaty[$idFaktury];
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$faktura['nazwa_przedsiebiorcy']}</td>";
        echo "<td style='padding: 8px;'>{$faktura['numer']}</td>";
        echo "<td style='padding: 8px;'>" . abs($wartoscNiedopłaty) . " PLN</td>";
        echo "</tr>";
    }
}

echo "</table>";
echo "<br><br>";

echo "<form method='GET' action=''>";
echo    "<label for='niedoplaty-sort'>Sortuj według:</label>";
echo    "<select name='niedoplaty-sort' id='niedoplaty-sort'>";
echo        "<option value=''>Wybierz sortowanie</option>";
echo        "<option value='numer_asc'>Sortuj według numeru faktury rosnąco</option>";
echo        "<option value='numer_desc'>Sortuj według numeru faktury malejąco</option>";
echo        "<option value='nazwa_asc'>Sortuj według nazwy klienta rosnąco</option>";
echo        "<option value='nazwa_desc'>Sortuj według nazwy klienta malejąco</option>";
echo    "</select>";
echo    "<input type='submit' value='Sortuj'>";
echo "</form>";

echo "<br><br>";
echo "<br><br>";

// Generowanie raportu z nierozliczonymi fakturami po terminie płatności

echo "<h2 style='margin: 0 0 5px 0;'>Raport z nierozliczonymi fakturami</h2>";

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr>";
echo "<th style='padding: 8px;'>Nazwa klienta</th>";
echo "<th style='padding: 8px;'>Numer faktury</th>";
echo "<th style='padding: 8px;'>Data terminu płatności</th>";
echo "<th style='padding: 8px;'>Data płatności</th>";
echo "</tr>";

foreach ($nieoplaconeFakturyDane as $dane) {
    echo "<tr>";
    echo "<td style='padding: 8px;'>{$dane['nazwa_przedsiebiorcy']}</td>";
    echo "<td style='padding: 8px;'>{$dane['numer']}</td>";
    echo "<td style='padding: 8px;'>{$dane['termin_platnosci']}</td>";
    echo "<td style='padding: 8px;'>" . ($dane['data_wplaty'] ?: 'Brak') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<br><br>";

echo "<form method='GET' action=''>";
echo    "<label for='sort'>Sortuj według:</label>";
echo    "<select name='nieoplacone-faktury-sort' id='nieoplacone-faktury-sort'>";
echo        "<option value=''>Wybierz sortowanie</option>";
echo        "<option value='numer_asc' " . ($sortOpcja == 'numer_asc' ? 'selected' : '') . ">Sortuj według numeru faktury rosnąco</option>";
echo        "<option value='numer_desc' " . ($sortOpcja == 'numer_desc' ? 'selected' : '') . ">Sortuj według numeru faktury malejąco</option>";
echo        "<option value='nazwa_asc' " . ($sortOpcja == 'nazwa_asc' ? 'selected' : '') . ">Sortuj według nazwy klienta rosnąco</option>";
echo        "<option value='nazwa_desc' " . ($sortOpcja == 'nazwa_desc' ? 'selected' : '') . ">Sortuj według nazwy klienta malejąco</option>";
echo        "<option value='termin_platnosci_asc' " . ($sortOpcja == 'termin_platnosci_asc' ? 'selected' : '') . ">Sortuj według terminu płatności rosnąco</option>";
echo        "<option value='termin_platnosci_desc' " . ($sortOpcja == 'termin_platnosci_desc' ? 'selected' : '') . ">Sortuj według terminu płatności malejąco</option>";
echo        "<option value='data_wplaty_asc' " . ($sortOpcja == 'data_wplaty_asc' ? 'selected' : '') . ">Sortuj według daty płatności rosnąco</option>";
echo        "<option value='data_wplaty_desc' " . ($sortOpcja == 'data_wplaty_desc' ? 'selected' : '') . ">Sortuj według daty płatności malejąco</option>";
echo    "</select>";
echo    "<input type='submit' value='Sortuj'>";
echo "</form>";
