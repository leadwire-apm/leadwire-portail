<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\ActivationCode;
use AppBundle\Manager\ActivationCodeManager;
use Hoa\Compiler\Llk\Llk;
use Hoa\File\Read;
use Hoa\Math\Sampler\Random;
use Hoa\Regex\Visitor\Isotropic;

class ActivationCodeService
{

    /**
     * @var string
     */
    private $rule;

    /**
     * @var ActivationCodeManager
     */
    private $acm;

    /**
     *
     * @param ActivationCodeManager $acm
     * @param string $rule
     */
    public function __construct(ActivationCodeManager $acm, string $rule)
    {
        $this->acm = $acm;
        $this->rule = $rule;
    }

    /**
     * @return ActivationCode
     */
    public function generateNewCode(): ActivationCode
    {
        $unique = false;

        $grammar = new Read('hoa://Library/Regex/Grammar.pp');
        $compiler = Llk::load($grammar);
        $ast = $compiler->parse($this->rule);
        $generator = new Isotropic(new Random());
        $code = "";

        // Make sure that the newly created code is unique in the DataBase
        while ($unique === false) {
            $code = $generator->visit($ast);
            $dbDocument = $this->acm->getOneBy(['code' => $code]);
            if ($dbDocument === null) {
                $unique = true;
            }
        }

        $activationCode = new ActivationCode();
        $activationCode
            ->setCode($code)
            ->setCreatedAt(new \DateTime());

        $this->acm->update($activationCode);

        return $activationCode;
    }

    public function validateActivationCode(ActivationCode $activationCode): bool
    {

        $valid = $activationCode->isUsed() === false && (bool) preg_match("/^{$this->rule}$/", $activationCode->getCode());

        return $valid;
    }

    public function getByCode(string $code): ?ActivationCode
    {
        return $this->acm->getOneBy(['code' => $code]);
    }
}
