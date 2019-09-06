<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

/**
 * This is a dummy interface with no methods which has the objective to abstract
 * signature authentication strategies:
 *   1. Backend authentication: when you already have username and password or;
 *   2. Frontend authentication: when you need to ask credentials to receive
 *      token before start transactions.
 */
interface AuthStrategy
{
}
