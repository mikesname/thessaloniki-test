<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/' route");
    $years = $this->db->query('SELECT DISTINCT year FROM study ORDER BY year')->fetchAll();
    $schools = $this->db->query('SELECT id, name FROM school')->fetchAll();

    return $this->view->render($response, "map.html", [
        "schools" => $schools,
        "years" => $years
    ]);
});

$app->get('/search', function (Request $request, Response $response, array $args) {
    $q = $this->db->prepare('
    SELECT 
        CAST(substring(substring_index(ST_AsWKT(a.loc), \' \', 1), 7) as DECIMAL(10,6)) as lon, 
        CAST(trim(trailing \')\' from substring_index(ST_AsWKT(a.loc), \' \', -1)) as DECIMAL(10,6)) as lat, 
        a.street, 
        p.name as name,
        s.name as school 
    FROM (person p, address a, school s) 
        JOIN person_address pa ON p.id = pa.person_id AND a.id = pa.id 
        JOIN study st ON p.id = st.person_id AND st.school_id = s.id 
    WHERE a.loc IS NOT NULL
      AND (:year = \'\' OR :year IS NULL OR st.year = :year)
      AND (:school = \'\' OR :school IS NULL OR s.id = :school)
      AND (:name = \'\' OR :name IS NULL OR MATCH(p.name) AGAINST(:name IN NATURAL LANGUAGE MODE))'
    );

    $params = [
        ":school" => $request->getQueryParam("school"),
        ":year" => $request->getQueryParam("year"),
        ":name" => $request->getQueryParam("q")
    ];
    $q->execute($params);

    return $response->withJson($q->fetchAll());
});

