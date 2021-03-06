<?php

/**
 * @file
 * Generate and print hash values of PHP files in specified directories.
 * This helps to confirm code behavior doesn't change after changing comments.
 *
 * - Comments and spaces are excluded.
 */

const EXTENSIONS = [
	'.inc',
	'.install',
	'.module',
	'.php',
];
const TOKENS_TO_IGNORE = [
	T_COMMENT,
	T_DOC_COMMENT,
	// Whitespaces can be ignored as well.
	T_WHITESPACE,
];
const TOKENS_TO_STRIP = [
	T_OPEN_TAG,
	T_CLOSE_TAG,
];

main();

/**
 * Main function.
 */
function main() 
{
	$targets = cli_args();

	foreach ($targets as $target) {
		walk_directory($target, function (string $full_path) {
			echo $full_path . ': ' . calc_token_hash($full_path) . PHP_EOL;
		});
	}
}

/**
 * Get CLI args.
 */
function cli_args() {
	$argv = $_SERVER['argv'];
	$script = array_shift($argv);
	return $argv;
}

/**
 * Traverse a directory recursively with DFS.
 */
function walk_directory(string $path, Callable $callback)
{
	if (!is_dir($path)) {
		throw new Exception("${path} is not a directory.");
	}	

	// Use `scandir()` as `DirectoryIterator`'s interface is better but
	// it cannot control the children order.
	foreach (scandir($path) as $entry) {
		if (in_array($entry, ['.', '..'], TRUE)) {
			continue;
		}

		$full_path = $path . DIRECTORY_SEPARATOR . $entry;

		if (is_target($full_path)) {
			$callback($full_path);
		}

		if (is_dir($full_path)) {
			walk_directory($full_path, $callback);	
		}
	}
}

/**
 * Check if the file is in the targets.
 */
function is_target(string $path): bool 
{
	foreach (EXTENSIONS as $extension) {
		$length = mb_strlen($extension);
		if (mb_substr($path, - $length) === $extension) {
			return TRUE;
		}
	} 	

	return FALSE;
}

/**
 * Generate hash value for PHP source after stripping comments.
 */
function calc_token_hash(string $path): string 
{
	$content = file_get_contents($path);
	$tokens = extract_tokens($content);
	return hash('sha256', implode(' ', $tokens));
}

/**
 * Extract tokens from code.
 */
function extract_tokens(string $content): array 
{
	$tokens = token_get_all($content);
	$filtered_tokens = [];
	foreach ($tokens as $token) {
		if (is_array($token)) {
			$token_index = $token[0];
			$part = $token[1];

			if (in_array($token_index, TOKENS_TO_IGNORE, true)) {
				continue;
			}

			if (in_array($token_index, TOKENS_TO_STRIP, true)) {
				$part = mb_ereg_replace('^\s+', '', $token[1]);	
				$part = mb_ereg_replace('\s+$', '', $part);	
			}

			$filtered_tokens[] = $part;
		} else {
			$filtered_tokens[] = $token;
		}
	}

	return $filtered_tokens;
}