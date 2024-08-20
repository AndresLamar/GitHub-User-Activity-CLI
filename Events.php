<?php

class Events
{
    private $username;

    // Definir constantes para los tipos de eventos
    const PUSH_EVENT = "PushEvent";
    const ISSUES_EVENT = "IssuesEvent";
    const WATCH_EVENT = "WatchEvent";
    const FORK_EVENT = "ForkEvent";
    const CREATE_EVENT = "CreateEvent";

    public function __construct($username)
    {
        $this->username = $username;
    }

    public function getEvents()
    {
        $url = "https://api.github.com/users/" . $this->username . "/events";

        $ch = $this->initializeCurl($url);
        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        curl_close($ch);

        if ($httpCode == 404) {
            throw new Exception("Error: Usuario no encontrado (404).");
        }

        if ($httpCode != 200) {
            throw new Exception("Error: La solicitud a la API falló con el código HTTP " . $httpCode . ".");
        }

        return json_decode($result, true);
    }

    private function initializeCurl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36'
        ]);
        return $ch;
    }

    public function displayEvents()
    {
        try {
            $events = $this->getEvents();
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return;
        }

        if (empty($events)) {
            echo "No hay eventos\n";
            return;
        }

        foreach ($events as $event) {
            echo "- " . $this->formatEventAction($event) . "\n";
        }
    }

    private function formatEventAction($event)
    {
        $action = "";
        switch ($event['type']) {
            case self::PUSH_EVENT:
                $commitCount = count($event['payload']['commits']);
                $action = "Pushed $commitCount commit(s) to {$event['repo']['name']}";
                break;
            case self::ISSUES_EVENT:
                $action = ucfirst($event['payload']['action']) . " an issue in {$event['repo']['name']}";
                break;
            case self::WATCH_EVENT:
                $action = "Starred {$event['repo']['name']}";
                break;
            case self::FORK_EVENT:
                $action = "Forked {$event['repo']['name']}";
                break;
            case self::CREATE_EVENT:
                $action = "Created {$event['payload']['ref_type']} in {$event['repo']['name']}";
                break;
            default:
                $eventType = str_replace("Event", "", $event['type']);
                $action = "{$eventType} in {$event['repo']['name']}";
                break;
        }
        return $action;
    }
}
