from pathlib import Path


# ============================================================
# CONFIG
# ============================================================

ROOT = Path(__file__).resolve().parent
OUTPUT = ROOT / "CODEBASE.txt"

# Set True if you want the directory tree.
# False = minimum possible token overhead.
INCLUDE_TREE = False

# Maximum individual file size to include.
MAX_FILE_SIZE_MB = 5


# ============================================================
# DIRECTORIES TO SKIP
# ============================================================

SKIP_DIRS = {
    # Version control
    ".git",

    # Dependencies
    "node_modules",
    "vendor",

    # Python
    "__pycache__",
    ".pytest_cache",
    ".mypy_cache",
    ".venv",
    "venv",
    "env",

    # Flutter / Dart
    ".dart_tool",
    "build",

    # Java / Android
    ".gradle",

    # IDE
    ".idea",
    ".vscode",

    # Build / generated
    "dist",
    "out",
    "target",
    "coverage",
}


# ============================================================
# FILES TO SKIP
# ============================================================

SKIP_FILES = {
    # This script
    "generate_codebase.py",

    # Output
    "CODEBASE.txt",
    "CODEBASE.md",

    # OS
    ".DS_Store",
    "Thumbs.db",
}


# ============================================================
# SECRET FILES
# ============================================================

SECRET_FILES = {
    ".env",
    ".env.local",
    ".env.development",
    ".env.production",
    ".env.test",

    "credentials.json",
    "secrets.json",
    "service-account.json",
    "firebase-adminsdk.json",
}


# ============================================================
# SECRET EXTENSIONS
# ============================================================

SECRET_EXTENSIONS = {
    ".pem",
    ".key",
    ".p12",
    ".pfx",
}


# ============================================================
# BINARY EXTENSIONS
# ============================================================

BINARY_EXTENSIONS = {
    # Images
    ".png",
    ".jpg",
    ".jpeg",
    ".gif",
    ".webp",
    ".bmp",
    ".ico",
    ".svg",

    # Audio
    ".mp3",
    ".wav",
    ".ogg",
    ".flac",

    # Video
    ".mp4",
    ".avi",
    ".mkv",
    ".mov",
    ".webm",

    # Archives
    ".zip",
    ".rar",
    ".7z",
    ".tar",
    ".gz",

    # Executables
    ".exe",
    ".dll",
    ".so",
    ".bin",

    # Compiled
    ".pyc",
    ".class",
    ".o",
    ".obj",

    # Databases
    ".db",
    ".sqlite",
    ".sqlite3",

    # Documents
    ".pdf",
    ".doc",
    ".docx",
    ".xls",
    ".xlsx",
    ".ppt",
    ".pptx",
}


# ============================================================
# SHOULD SKIP
# ============================================================

def should_skip(path: Path):

    if path.name in SKIP_FILES:
        return True

    if path.name in SECRET_FILES:
        return True

    if path.suffix.lower() in SECRET_EXTENSIONS:
        return True

    if path.suffix.lower() in BINARY_EXTENSIONS:
        return True

    return False


# ============================================================
# GET FILES
# ============================================================

def get_files():

    files = []

    for path in ROOT.rglob("*"):

        if not path.is_file():
            continue

        relative = path.relative_to(ROOT)

        # Skip excluded directories
        if any(
            part in SKIP_DIRS
            for part in relative.parts[:-1]
        ):
            continue

        # Skip files
        if should_skip(path):
            continue

        # Skip huge files
        try:
            size = path.stat().st_size

            if size > MAX_FILE_SIZE_MB * 1024 * 1024:
                print(
                    f"SKIP large: {relative} "
                    f"({size / 1024 / 1024:.1f} MB)"
                )
                continue

        except OSError:
            continue

        files.append(path)

    return sorted(
        files,
        key=lambda x: str(x).lower()
    )


# ============================================================
# READ TEXT FILE
# ============================================================

def read_text(path: Path):

    try:
        return path.read_text(
            encoding="utf-8"
        )

    except UnicodeDecodeError:
        return None

    except Exception as e:
        print(f"ERROR: {path} -> {e}")
        return None


# ============================================================
# TREE
# ============================================================

def create_tree(files):

    tree = {}

    for path in files:

        relative = path.relative_to(ROOT)

        current = tree

        for part in relative.parts:

            current = current.setdefault(
                part,
                {}
            )

    lines = [ROOT.name + "/"]

    def walk(node, prefix=""):

        items = list(node.items())

        for i, (name, children) in enumerate(items):

            last = i == len(items) - 1

            lines.append(
                prefix
                + ("└── " if last else "├── ")
                + name
            )

            if children:

                walk(
                    children,
                    prefix
                    + ("    " if last else "│   ")
                )

    walk(tree)

    return "\n".join(lines)


# ============================================================
# GENERATE
# ============================================================

def generate():

    print("Scanning:", ROOT)

    files = get_files()

    print("Files:", len(files))

    output = []

    # --------------------------------------------------------
    # Optional tree
    # --------------------------------------------------------

    if INCLUDE_TREE:

        output.append(
            "TREE\n"
        )

        output.append(
            create_tree(files)
        )

        output.append(
            "\n\n"
        )

    # --------------------------------------------------------
    # Files
    # --------------------------------------------------------

    for i, path in enumerate(files, 1):

        relative = path.relative_to(ROOT)

        print(
            f"[{i}/{len(files)}] {relative}"
        )

        content = read_text(path)

        if content is None:

            print(
                f"  SKIP binary/unreadable: {relative}"
            )

            continue

        # Compact file marker.
        #
        # @path
        # contents
        #
        # Nothing else is added.

        output.append(
            f"@{relative.as_posix()}\n"
        )

        output.append(content)

        # Ensure next file starts on a new line.
        if not content.endswith("\n"):
            output.append("\n")

        output.append("\n")

    # --------------------------------------------------------
    # Write
    # --------------------------------------------------------

    OUTPUT.write_text(
        "".join(output),
        encoding="utf-8"
    )

    size_mb = OUTPUT.stat().st_size / 1024 / 1024

    print()
    print("=" * 50)
    print("DONE")
    print("=" * 50)
    print("Output:", OUTPUT)
    print("Files:", len(files))
    print(f"Size: {size_mb:.2f} MB")
    print("=" * 50)


# ============================================================
# RUN
# ============================================================

if __name__ == "__main__":
    generate()