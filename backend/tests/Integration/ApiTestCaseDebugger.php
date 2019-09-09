<?php

namespace App\Tests\Integration;

use Symfony\Component\BrowserKit\History;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCaseDebugger
{
    /**
     * @var History
     */
    private static $history;

    /**
     * @var ApiClient
     */
    private static $client;

    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * ApiTestCaseDebugger constructor.
     *
     * @param ApiClient $client
     */
    public function __construct(ApiClient $client)
    {
        self::$history = $client->getHistory();
        self::$client = $client;
    }

    public function printLastRequestUrl()
    {
        $lastRequest = self::$history->current();

        if ($lastRequest) {
            $this->printDebug(sprintf('<comment>%s</comment>: <info>%s</info>', $lastRequest->getMethod(), $lastRequest->getUri()));
        } else {
            $this->printDebug('No request was made.');
        }
    }

    public function debugResponse(Response $response)
    {
        $this->printDebug($this->getStartLineAndHeaders($response));
        $body = (string) $response->getContent();
        $contentType = $response->headers->get('Content-Type');

        if ($contentType == 'application/json' || strpos($contentType, '+json') !== false) {
            $data = json_decode($body);

            if ($data === null) {
                $this->printDebug($body);
            } else {
                $this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
            }
        } else {
            $isValidHtml = strpos($body, '</body>') !== false;

            if ($isValidHtml) {
                $this->printDebug('');
                $crawler = new Crawler($body);

                $this->printErrorBlock('There was an Error! Details:');

                foreach ($crawler->filter('h1, h2')->extract(['_text']) as $header) {
                    if (strpos($header, 'Stack Trace') !== false) {
                        continue;
                    }

                    if (strpos($header, 'Logs') !== false) {
                        continue;
                    }

                    if (strpos($header, 'Symfony Exception') !== false) {
                        continue;
                    }

                    $header = str_replace("\n", ' ', trim($header));
                    $header = preg_replace('/(\s)+/', ' ', $header);

                    $this->printErrorBlock($header);
                }

                /*
                 * When using the test environment, the profiler is not active
                 * for performance. To help debug, turn it on temporarily in
                 * the config_test.yml file (framework.profiler.collect)
                 */
                $profilerUrl = $response->headers->get('X-Debug-Token-Link');
                if ($profilerUrl) {
                    $fullProfilerUrl = $response->headers->get('Host').$profilerUrl;
                    $this->printDebug('');
                    $this->printDebug(sprintf('Profiler URL: <comment>%s</comment>', $fullProfilerUrl));
                }

                // an extra line for spacing
                $this->printDebug('');
            } else {
                $this->printErrorBlock($body);
            }
        }
    }

    /**
     * Print a message out - useful for debugging.
     *
     * @param $string
     */
    public function printDebug($string)
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }

        $this->output->writeln($string);
    }

    /**
     * Print a debugging message out in a big red block.
     *
     * @param $string
     */
    public function printErrorBlock($string)
    {
        if ($this->formatterHelper === null) {
            $this->formatterHelper = new FormatterHelper();
        }
        $output = $this->formatterHelper->formatBlock($string, 'bg=red;fg=white', true);

        $this->printDebug($output);
    }

    /**
     * @return Response
     */
    public function getLastResponse(): Response
    {
        return self::$client->getResponse();
    }

    /**
     * Gets the start-line and headers of a message as a string.
     *
     * @param Response|Request $message
     *
     * @return string
     */
    private static function getStartLineAndHeaders($message)
    {
        return static::getStartLine($message).self::getHeadersAsString($message);
    }

    /**
     * Gets the headers of a message as a string.
     *
     * @param Response|Request $message
     *
     * @return string
     */
    private static function getHeadersAsString($message)
    {
        $result = '';

        foreach ($message->headers as $name => $values) {
            $result .= "\r\n{$name}: ".implode(', ', $values);
        }

        return $result;
    }

    /**
     * Gets the start line of a message.
     *
     * @param Request|Response $message
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private static function getStartLine($message)
    {
        if ($message instanceof Request) {
            return trim($message->getMethod().' '.$message->getUri()).' HTTP/'.$message->getProtocolVersion();
        } elseif ($message instanceof Response) {
            return 'HTTP/'.$message->getProtocolVersion().' '.$message->getStatusCode();
        } else {
            throw new \InvalidArgumentException('Unknown message type');
        }
    }
}
