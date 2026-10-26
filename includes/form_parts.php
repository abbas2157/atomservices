<?php
/**
 * Pieces every AJAX form shares: the spam honeypot, the status line (which also shows the result of a
 * non-JavaScript submission) and the success panel main.js swaps in after sending.
 */
require_once __DIR__ . '/contact_handler.php';

/** Hidden field only bots fill in. $prefix keeps ids unique when a page has several forms. */
function form_honeypot(string $prefix): string {
  return '<div class="hp" aria-hidden="true"><label for="' . $prefix . '-website">Website</label>'
    . '<input id="' . $prefix . '-website" name="website" tabindex="-1" autocomplete="off"></div>';
}

/** Status line; filled in server-side when the page is loaded after a plain form post. */
function form_status(string $form): string {
  $status = contact_status_message($_GET['form_status'] ?? null, $form);
  return '<p class="form-msg' . ($status ? ' ' . $status[0] : '') . '" role="status" aria-live="polite"'
    . ($status ? '>' . e($status[1]) : ' hidden>') . '</p>';
}

function form_success(string $title, string $again): string {
  return '<div class="form-success" tabindex="-1" hidden>'
    . '<span class="success-ic" aria-hidden="true">&#10003;</span>'
    . '<h3>' . e($title) . '</h3><p class="form-success-text"></p>'
    . '<button class="btn btn-ghost" type="button" data-send-another>' . e($again) . '</button></div>';
}
