<?php

namespace App\WebSocket;

require_once("../manager/ConfigManager.php");

use App\Manager\ConfigManager;

class WebSocketServer
{
    private $config;
    private $table;
    private $server;

    public function __construct()
    {
        //内存表，进程间共享数据
        $this->createTable();
        $this->config = ConfigManager::getInstance();


        var_dump($this->config["socket"]);
    }

    public function run()
    {
        $this->server = new \swoole_websocket_server(
            $this->config['socket']['host'],
            $this->config['socket']['port']
        );

        $this->server->on('open', [$this, 'open']);
        $this->server->on('request', [$this, 'request']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('close', [$this, 'close']);

        $this->server->start();
    }

    public function open(\swoole_websocket_server $serv, \swoole_http_request $req)
    {
        $user = [
            'fd' => $req->fd,
            'name' => $this->config['socket']['name'][array_rand($this->config['socket']['name'])] . $req->fd,
            'avatar' => $this->config['socket']['avatar'][array_rand($this->config['socket']['avatar'])],
        ];
        $this->table->set($req->fd, $user);

        $serv->push($req->fd, json_encode(
            array_merge(['user' => $user], ['all' => $this->allUser()], ['type' => 'openSuccess'])
        ));
    }


    public function message(\swoole_websocket_server $server, $frame)
    {

    }

    public function request(\swoole_http_request $req, \swoole_http_response $res)
    {
        foreach ($this->server->connections as $fd) {
            if ($this->server->isEstablished($fd)) {
                $this->server->push($fd, "haha!");
            }
        }
        // $res->end("hihi!");
    }

    public function close(\swoole_websocket_server $server, $fd)
    {
        echo "client {$fd} closed\n";
    }

    private function allUser()
    {
        $users = [];
        foreach ($this->table as $row) {
            $users[] = $row;
        }
        return $users;
    }

    private function createTable()
    {
        $this->table = new \swoole_table(1024);
        $this->table->column('fd', \swoole_table::TYPE_INT);
        $this->table->column('name', \swoole_table::TYPE_STRING, 255);
        $this->table->column('avatar', \swoole_table::TYPE_STRING, 255);
    }
}

(new WebSocketServer())->run();
