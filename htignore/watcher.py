from glob import glob

for p in glob(r"universe-images/*/*/gallery/main.*"):
    p = p.replace('\\', '/')
    print(p)
pass
