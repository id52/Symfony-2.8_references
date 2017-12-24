<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Order as OrderModel;

class Order extends OrderModel
{
    # Статусы ордеров:
    #
    # createdByTreasurer        -   создан казначеем
    # issuedByOperator          -   выдан оператором
    # issuedForkedByOperator    -   сабордер выдан оператором
    # forkedByOrderman          -   сабордер создан ордеристом
    # closedByOrderman          -   закрыт ордеристом
    # closedBySupervisor        -   закрыт супервайзером
}
