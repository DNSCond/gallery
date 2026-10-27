"""
custom_format.py - Python port of customFormat.php

Parses and stringifies a simple "named blocks" text format:

    name: first
    some content
    more content
    ---
    name: second
    other content

Behavior is kept the same as the PHP original, including its quirks:

* Line boundaries are only "\\r\\n", "\\n" and "\\r" (same as the PHP regex),
  not the larger set used by Python's str.splitlines().
* When splitting without line endings, EMPTY lines are dropped
  (PHP's PREG_SPLIT_NO_EMPTY). This means blank lines inside blocks are
  removed during parsing and normalization.
* A separator line must be exactly "---" (no surrounding whitespace).
* A block header matches /^name: ?(.+)/i (ASCII case-insensitive). The
  captured name is trimmed with PHP's trim() character set.
  A first line of just "name:" does NOT match, so that block is unnamed.
* Blocks without a header are stored under the key "" (empty string).
* Blocks whose content is empty/whitespace-only (after PHP trim) are skipped.
* Duplicate handling: 'last' (default, also used for unknown values),
  'error', 'array', or 'concat'.

One unavoidable difference: PHP turns numeric-string array keys such as
"123" into integers. This port always uses string keys.
"""

from __future__ import annotations

import re
from typing import Any, Callable, Dict, List, Mapping, Optional, Sequence, TypeVar, Union

__all__ = [
    "split_lines_python_style",
    "normalize_newlines",
    "chunk_array_at",
    "parse_named_blocks",
    "stringify",
]

T = TypeVar("T")

# Same as PHP '/\r\n|[\n\r]/'
_LINE_BOUNDARY = re.compile(r"\r\n|[\n\r]")

# Same as PHP '/^name: ?(.+)/i' (no /u flag, so ASCII-only case folding)
_NAME_HEADER = re.compile(r"name: ?(.+)", re.IGNORECASE | re.ASCII)

# Characters stripped by PHP's trim(): space, \t, \n, \r, \0, \x0B
_PHP_TRIM_CHARS = " \t\n\r\0\x0b"


def _php_trim(value: str) -> str:
    return value.strip(_PHP_TRIM_CHARS)


def _php_to_string(value: Any) -> str:
    """Approximate PHP's (string) cast for common scalar types."""
    if value is None:
        return ""
    if value is True:
        return "1"
    if value is False:
        return ""
    return str(value)


def split_lines_python_style(string: str, keep_ends: bool = False) -> List[str]:
    """Split a string on \\r\\n, \\n or \\r, optionally keeping the separators.

    With keep_ends=False, empty pieces are discarded (PREG_SPLIT_NO_EMPTY).
    """
    if keep_ends:
        result: List[str] = []
        last_index = 0
        for match in _LINE_BOUNDARY.finditer(string):
            result.append(string[last_index:match.end()])
            last_index = match.end()
        if last_index < len(string):
            result.append(string[last_index:])
        return result

    return [part for part in _LINE_BOUNDARY.split(string) if part != ""]


def normalize_newlines(of: str) -> str:
    """Normalize newlines by splitting and rejoining with \\n (drops empty lines)."""
    return "\n".join(split_lines_python_style(of))


def chunk_array_at(
        array: Sequence[T],
        fn: Callable[[T, int, Sequence[T]], bool],
        skip_at_split: bool = False,
) -> List[List[T]]:
    """Chunk a sequence, starting a new chunk at each element where fn(...) is true.

    fn receives (element, index, array), like the PHP version.
    """
    result: List[List[T]] = [[]]
    current = result[0]
    for index, element in enumerate(array):
        if fn(element, index, array):
            current = []
            result.append(current)
            if skip_at_split:
                continue
        current.append(element)
    return result


BlockValue = Union[str, List[str]]


def parse_named_blocks(
        string: str,
        options: Optional[Mapping[str, Any]] = None,
        *,
        duplicate_disambiguation: Optional[str] = None,
        concat_separator: Any = None,
) -> Dict[str, BlockValue]:
    """Parse named blocks from a string.

    Options can be given PHP-style via ``options`` (keys
    'duplicateDisambiguation' and 'concatSeperator') or via the keyword
    arguments, which take precedence.

    duplicate_disambiguation:
        'last'   - (default) later block overwrites earlier one
        'error'  - raise ValueError on a duplicate key
        'array'  - every value is a list of strings
        'concat' - join duplicates with concat_separator (default "\\n\\n")
    """
    options = options or {}

    mode = duplicate_disambiguation
    if mode is None:
        mode = options.get("duplicateDisambiguation")
    if mode is None:
        mode = "last"

    sep_raw = concat_separator
    if sep_raw is None:
        sep_raw = options.get("concatSeperator")
    if sep_raw is None:
        sep_raw = "\n\n"
    separator = _php_to_string(sep_raw)

    result: Dict[str, Any] = {}

    def apply(key: str, string_value: str) -> None:
        if _php_trim(string_value) == "":
            return

        if mode == "error":
            if key in result:
                raise ValueError(f'key "{key}" appears more than once')
        elif mode == "array":
            if key in result:
                result[key].append(string_value)
            else:
                result[key] = [string_value]
            return
        elif mode == "concat":
            if key in result:
                result[key] += separator + string_value
            else:
                result[key] = string_value
            return

        result[key] = string_value

    chunks = chunk_array_at(
        split_lines_python_style(string),
        lambda s, *_: s == "---",
        True,
    )

    for element in chunks:
        if len(element) == 0:
            continue

        match = _NAME_HEADER.match(element[0])
        if match:
            key = _php_trim(match.group(1))
            apply(key, "\n".join(element[1:]))
        else:
            apply("", "\n".join(element))

    return result


def stringify(obj: Mapping[Any, Any]) -> str:
    """Stringify a named-blocks mapping back into the text format.

    List/tuple values produce one block per item under the same name.
    """
    parts: List[str] = []
    for key, val in obj.items():
        key_str = _php_to_string(key)
        if isinstance(val, (list, tuple)):
            for v in val:
                parts.append(f"name: {key_str}\n" + normalize_newlines(_php_to_string(v)))
        else:
            parts.append(f"name: {key_str}\n" + normalize_newlines(_php_to_string(val)))
    return "\n---\n".join(parts)


pass
