#!/usr/bin/env python3
"""Local render helper.

Usage:
  python render_local.py [path]

If a path is provided, the script prints the file contents with a local render header.
If no path is provided, it reads from stdin and prints the content back.
"""

import argparse
import sys


def render_text(text: str) -> str:
    return "Rendered from local:\n" + text


def main() -> int:
    parser = argparse.ArgumentParser(description="Render input locally.")
    parser.add_argument("path", nargs="?", help="Optional file path to render")
    args = parser.parse_args()

    if args.path:
        try:
            with open(args.path, "r", encoding="utf-8") as f:
                content = f.read()
        except FileNotFoundError:
            print(f"Error: file not found: {args.path}", file=sys.stderr)
            return 1
        except OSError as exc:
            print(f"Error: could not read file: {exc}", file=sys.stderr)
            return 1
    else:
        content = sys.stdin.read()

    output = render_text(content)
    print(output, end="")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
