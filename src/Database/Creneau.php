<?php

namespace Database;
require_once dirname(__DIR__) . '/Database/Connect.php';

class CreneauModel extends Database
{
    public function getCreneaux()
    {
        return $this->select("SELECT * FROM creneaux;");
    }

    public function createCreneau(string $creneau)
    {
        echo json_encode($creneau);
        $this->insert("INSERT INTO creneaux(creneau) VALUES('" . json_decode(json_encode($creneau)) ."')");
    }

    public function getCreneauxASC()
    {
        return $this->select("SELECT creneau->'start' FROM creneaux ORDER BY creneau ->> 'start' ASC;");
    }
}
