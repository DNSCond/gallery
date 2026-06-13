import pathlib

print(pathlib.Path('images'))
for path in pathlib.Path('images').glob('*/*.watermarked.watermarked.*'):
    print(path)
    path.unlink()
pass
