<?php

namespace zonuexe\Httpbin;

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
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Authorization' => "Bearer {$this->access_token}",
                    'User-Agent' => sprintf('php/%s; zonuexe\\httpbin', PHP_VERSION),
                ],
                'ignore_errors' => true,
            ],
        ]);
        $response = file_get_contents('https://httpbin.org/get?' . http_build_query([
            'name' => $name,
            'authors' => (array)$authors,
            'amount' => rand(1, 100),
        ]), false, $context);

        $data = [];
        foreach (json_decode($response, true)['args'] as $k => $value) {
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
