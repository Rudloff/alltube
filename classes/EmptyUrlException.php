<?php
/**
 * EmptyUrlException class.
 */

namespace Alltube;

use Exception;

/**
 * Exception thrown when youtube-dl returns an empty URL.
 */
class EmptyUrlException extends Exception
{
}
