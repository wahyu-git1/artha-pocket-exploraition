<?php

namespace App\Enums;

/**
 * Centralized API error codes.
 * Used in ApiResponse::error() calls throughout the app.
 */
class ErrorCode
{
    // --- General ---
    const VALIDATION_ERROR    = 'VALIDATION_ERROR';
    const UNAUTHORIZED        = 'UNAUTHORIZED';
    const TOKEN_EXPIRED       = 'TOKEN_EXPIRED';
    const TOKEN_INVALID       = 'TOKEN_INVALID';
    const FORBIDDEN           = 'FORBIDDEN';
    const NOT_FOUND           = 'NOT_FOUND';
    const CONFLICT            = 'CONFLICT';
    const RATE_LIMITED        = 'RATE_LIMITED';
    const SERVER_ERROR        = 'SERVER_ERROR';

    // --- Auth ---
    const EMAIL_TAKEN         = 'EMAIL_TAKEN';
    const INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    const WRONG_PASSWORD      = 'WRONG_PASSWORD';

    // --- Income ---
    const HAS_TRANSACTIONS    = 'HAS_TRANSACTIONS';
    const INSUFFICIENT_BALANCE = 'INSUFFICIENT_BALANCE';

    // --- Category ---
    const DEFAULT_READONLY    = 'DEFAULT_READONLY';
    const DUPLICATE_NAME      = 'DUPLICATE_NAME';
    const IN_USE              = 'IN_USE';

    // --- Smart Entry ---
    const PARSE_NO_AMOUNT     = 'PARSE_NO_AMOUNT';
    const INSUFFICIENT_DATA   = 'INSUFFICIENT_DATA';

    // --- Savings Goal ---
    const GOAL_COMPLETED      = 'GOAL_COMPLETED';

    // --- Emergency Fund ---
    const ALREADY_EXISTS      = 'ALREADY_EXISTS';
    const INSUFFICIENT_FUND   = 'INSUFFICIENT_FUND';

    // --- Allocation ---
    const SUM_NOT_100         = 'SUM_NOT_100';

    // --- Sync ---
    const INVALID_SINCE       = 'INVALID_SINCE';
}
