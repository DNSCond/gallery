# from glob import glob
# import pathlib
#
# for p in glob(r"D:\var\www\BOTs\gallery\htignore\universe-images\*/*/*"):
#     p = p.replace('\\', '/')
#     if pathlib.Path(p).is_dir():
#         continue
#     t = p.replace('D:/var/www/BOTs/gallery/', 'D:/var/www/BOTs/gallery/')
#     ((to := pathlib.Path(t)).parent / 'gallery' / 'ai').mkdir(exist_ok=True, parents=True)
#     if to.name.startswith('ai.gallery'):
#         t = str((to.parent / 'gallery' / 'ai' / '.'.join(to.name.split('.')[2:])))
#     elif to.name.startswith('gallery'):
#         t = str((to.parent / 'gallery' / '.'.join(to.name.split('.')[1:])))
#     with open(p, 'rb') as src, open(t, 'wb') as out:
#         out.write(src.read())
# for p in glob(r"D:\var\www\BOTs\gallery\htignore\images\*/*"):
#     p = p.replace('\\', '/')
#     if pathlib.Path(p).is_dir():
#         continue
#     t = p.replace(
#         'D:/var/www/BOTs/gallery/htignore/images/',
#         'D:/var/www/BOTs/gallery/htignore/universe-images/main/')
#     ((to := pathlib.Path(t)).parent / 'gallery' / 'ai').mkdir(exist_ok=True, parents=True)
#     if to.name.startswith('ai.gallery'):
#         t = str((to.parent / 'gallery' / 'ai' / '.'.join(to.name.split('.')[2:])))
#     elif to.name.startswith('gallery'):
#         t = str((to.parent / 'gallery' / '.'.join(to.name.split('.')[1:])))
#     with open(p, 'rb') as src, open(t, 'wb') as out:
#         out.write(src.read())
# pass
# """
# D:/var/www/BOTs/gallery/htignore/universe-images/AttachedEdu/katera/nomark
# D:/var/www/BOTs/gallery/htignore/universe-images/AttachedEdu/split-3/no-submit
# """
