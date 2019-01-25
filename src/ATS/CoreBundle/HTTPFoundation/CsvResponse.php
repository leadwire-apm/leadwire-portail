<?php declare(strict_types=1);

namespace ATS\CoreBundle\HTTPFoundation;

use Symfony\Component\HttpFoundation\Response;

class CsvResponse extends Response
{

    const DEFAULT_FILE_NAME = 'export.csv';

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * Ctor
     *
     * @param mixed $data
     * @param string $fileName
     * @param int $status
     * @param array $headers
     */
    public function __construct(
        $data = null,
        $fileName = self::DEFAULT_FILE_NAME,
        $status = Response::HTTP_OK,
        $headers = array()
    ) {
        parent::__construct('', $status, $headers);

        if (null === $data) {
            $data = new \ArrayObject();
        }

        $this->setData($data);
        $this->fileName = $fileName;
    }

    /**
     *
     * @param mixed $data
     *
     * @return CsvResponse
     */
    public function setData($data)
    {
        if (is_array($data) === true) {
            foreach ($data as $row) {
                $this->data .= implode(',', $row)."\n";
            }
        }

        return $this->update();
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return CsvResponse
     */
    protected function update()
    {
        $this->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s"', $this->fileName)
        );

        $this->headers->set('Content-Encoding', 'UTF-8');
        $this->headers->set('Content-Type', 'text/csv; charset=UTF-8');

        return $this->setContent($this->data);
    }
}
