<?php

namespace app\interfaces\UserRelation;

use yii\base\InvalidCallException;

trait UserRelationTrait
{
    /**
     * @param ByOwnerInterface|ByDebtInterface $modelSource
     *
     * @return self
     */
    public function setUsers($modelSource): self
    {
        if ($modelSource instanceof ByOwnerInterface && $this instanceof ByOwnerInterface) {
            $ownerUID = $modelSource->ownerUID();
            $linkedUID = $modelSource->linkedUID();

            $this->ownerUID($ownerUID);
            $this->linkedUID($linkedUID);
        } elseif ($modelSource instanceof ByDebtInterface && $this instanceof ByDebtInterface) {
            $debtReceiverUID = $modelSource->debtReceiverUID();
            $debtorUID = $modelSource->debtorUID();

            $this->debtReceiverUID($debtReceiverUID);
            $this->debtorUID($debtorUID);
        } else {
            throw new InvalidCallException();
        }

        return $this;
    }
}
