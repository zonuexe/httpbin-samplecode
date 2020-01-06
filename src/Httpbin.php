<?php

namespace zonuexe\Httpbin;

use GuzzleHttp\Client as HttpClient;

final class Httpbin
{
    /** @var string */
    private $access_token;

    public function __construct()
    {
        $this->access_token = 'abcdefghijklmn';
    }

    /**
     * @param string $name 書名
     * @param string|array<string> $authors 著者
     */
    public function getByNameAndAuthors(string $name, $authors): Book
    {
        $client = new HttpClient;
        $response = $client->request('GET', 'https://httpbin.org/get', [
            'header' => [
                'Authorization' => "Bearer {$this->access_token}",
                'User-Agent' => sprintf('php/%s; zonuexe\\httpbin', PHP_VERSION),
            ],
            'query' => [
                'name' => $name,
                'authors' => (array)$authors,
                'amount' => rand(1, 100),
            ],
            'http_errors' => false,
        ]);

        $data = [];
        foreach (json_decode((string)$response->getBody(), true)['args'] as $k => $value) {
            preg_match('/\A(?<key>[^[]+)(?:\[(?<nestkeys>.+)\])?\z/', $k, $matches);

            $key = $matches['key'];
            if (isset($matches['nestkeys'])) {
                $keys = array_reverse(explode('][', $matches['nestkeys']));
                array_push($keys, $key);

                $f = function (array &$ary, array $keys) use (&$f, $value) {
                    $key = array_pop($keys);

                    if (!isset($ary[$key])) {
                        $ary[$key] = [];
                    }

                    if ($keys) {
                        $f($ary[$key], $keys);
                    } else {
                        $ary[$key] = $value;
                    }
                };

                $f($data, $keys);
            } else {
                $data[$key] = $value;
            }
        }

        return Book::fromArray($data);
    }
}
