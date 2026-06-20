import asyncio, aiohttp, aiofiles
from urllib.parse import quote
import pathlib

sem = asyncio.Semaphore(30)
paths = [
    # '404placeholder.png',
    *pathlib.Path('images').glob('*/*.*'),
    # *pathlib.Path('comic-images').glob('*/*/*.*'),
    *pathlib.Path('universe-images').glob('*/*/*.*'),
    *pathlib.Path('universe-images').glob('*/*.*'),
    'images/universe-img.png',
]


async def fetch(path: str, session: aiohttp.ClientSession):
    p = pathlib.Path(path)

    if path.count('watermarked'):
        return
    path = p
    if path.suffix not in ['.jpg', '.jpeg', '.png', '.webp', '.avif']: return
    new_path = p.with_name(f"{path.stem}.watermarked{path.suffix}")
    if new_path.exists(): return
    async with sem:
        urlpath = ('https://localhost/gallery/img-convert.' +
                     f'php?img-path={quote(str(p.resolve()))}'
                     + f'&format=res{path.suffix}')
        async with session.get(urlpath) as resp:
            resp.raise_for_status()
            async with aiofiles.open(new_path, 'wb') as file:
                await file.write(await resp.read())
        pass
    pass


async def main():
    print('starting job')
    async with aiohttp.ClientSession() as session:
        async with asyncio.TaskGroup() as taskgrp:
            for path in paths:
                taskgrp.create_task(fetch(str(path).replace('\\', '/'), session))
            pass
    pass


try:
    asyncio.run(main())
except* Exception as eg:
    for exc in eg.exceptions:
        print()
        print(repr(exc))
pass
