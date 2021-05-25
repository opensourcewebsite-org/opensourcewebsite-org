<?php
/**
 * (c) Nex-Otaku (https://github.com/Nex-Otaku)
 */
namespace app\helpers;

use yii\web\BadRequestHttpException;
use yii\web\Request;

class RequestFetcher
{
    private const FETCH_MODE_AUTO = 'auto';

    private const FETCH_MODE_GET = 'get';

    private const FETCH_MODE_POST = 'post';

    private ?string $name = null;

    private bool $isSetParameter = false;

    private bool $isOptional = false;

    private string $fetchMode = self::FETCH_MODE_AUTO;

    public function parameter(string $name): self
    {
        $this->name = $name;
        $this->isSetParameter = true;
        $this->isOptional = false;
        $this->fetchMode = self::FETCH_MODE_AUTO;

        return clone $this;
    }

    public function optional(): self
    {
        $this->checkParameterIsSet();
        $this->isOptional = true;

        return $this;
    }

    public function required(): self
    {
        $this->checkParameterIsSet();
        $this->isOptional = false;

        return $this;
    }

    public function fromGet(): self
    {
        $this->checkParameterIsSet();
        $this->fetchMode = self::FETCH_MODE_GET;

        return $this;
    }

    public function fromPost(): self
    {
        $this->checkParameterIsSet();
        $this->fetchMode = self::FETCH_MODE_POST;

        return $this;
    }

    public function fromAny(): self
    {
        $this->checkParameterIsSet();
        $this->fetchMode = self::FETCH_MODE_AUTO;

        return $this;
    }

    public function integer(): ?int
    {
        return $this->getTypedValue(function ($value) {
            if (is_int($value)) {
                return $value;
            }

            if (!is_string($value) || !ctype_digit($value)) {
                throw new BadRequestHttpException('Unsupported format of ' . $this->name);
            }

            return (int)$value;
        });
    }

    public function string(): ?string
    {
        return $this->getTypedValue(function ($value) {
            if (!is_string($value)) {
                throw new BadRequestHttpException('Unsupported format of ' . $this->name);
            }

            return $value;
        });
    }

    public function array(): ?array
    {
        return $this->getTypedValue(function ($value) {
            if (!is_array($value)) {
                throw new BadRequestHttpException('Unsupported format of ' . $this->name);
            }

            return $value;
        });
    }

    private function checkParameterIsSet(): void
    {
        if (!$this->isSetParameter) {
            throw new \LogicException('You must set parameter first');
        }
    }


    private function getTypedValue(\Closure $callback)
    {
        $this->checkParameterIsSet();
        $value = $this->fetchValue();

        if ($value === null) {
            if (!$this->isOptional) {
                throw new BadRequestHttpException('Missing required ' . $this->name);
            }

            return $value;
        }

        return $callback->call($this, $value);
    }

    private function fetchValue()
    {
        $request = \Yii::$app->request;

        if (!($request instanceof Request)) {
            throw new \LogicException('Unsupported request object type');
        }

        if ($this->fetchMode === self::FETCH_MODE_GET) {
            return $request->get($this->name);
        }

        if ($this->fetchMode === self::FETCH_MODE_POST) {
            return $request->post($this->name);
        }

        if ($this->fetchMode === self::FETCH_MODE_AUTO) {
            $getParams = $request->get();

            if (array_key_exists($this->name, $getParams)) {
                return $getParams[$this->name];
            }

            $postParams = $request->post();

            if (array_key_exists($this->name, $postParams)) {
                return $postParams[$this->name];
            }

            return null;
        }

        throw new \LogicException('Unsupported fetch type');
    }
}
