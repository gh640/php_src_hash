# PHP source hash 

A simple PHP script to calculate and print hash values for PHP source files. This can help to check if two source files are semantically equivalent.

## Usage

```bash
php php_src_hash.php [target_directory]
```

## Example

```bash
$ php php_src_hash.php examples
examples/hello.php: bcad0f62cf3de37aee03ca80ca877b027997297808ca806a77d648f6ee03d143
examples/hello_with_comments.php: bcad0f62cf3de37aee03ca80ca877b027997297808ca806a77d648f6ee03d143
examples/hello_with_minimal_spaces.php: bcad0f62cf3de37aee03ca80ca877b027997297808ca806a77d648f6ee03d143
```