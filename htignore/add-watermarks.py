import asyncio, aiohttp
import datetime
import pathlib
import re, jwt

sem = asyncio.Semaphore(75)

secret = "026c56c425825f58b24b96f3ea54dfa46b563a4139687039e837e2824868c75bc5fb6ca2e07e41be46b830faeedb0c99a6c42ed12d6e14a39748bdf55802e827ca5526"
iat = datetime.datetime.now(datetime.UTC)
payload = {
    "iat": iat, 'nbf': iat, "exp": iat + datetime.timedelta(hours=1),
    'data': dict(mustredraw=True),  # 'nowatermark': True,
}
token = jwt.encode(payload, secret, algorithm="HS256")


async def fetch(path: str, session: aiohttp.ClientSession):
    matched = re.search('^images/([^./]+)/([^/.]+)\\.(png|jpe?g|webp|avif)$', path)
    p = pathlib.Path(path)
    new_name = None
    image = None
    if matched:
        image = f'{matched.group(1)}.{matched.group(2)}.{matched.group(3)}'
        new_name = matched.group(2) + ".watermarked" + p.suffix
    else:
        matched = re.search('^images/([^./]+)/([^/.]+)\\.([^/.]+)\\.(png|jpe?g|webp|avif)$', path)
        if matched:
            image = f'{matched.group(2)}.{matched.group(1)}.{matched.group(3)}.{matched.group(4)}'
            new_name = f'{matched.group(2)}.{matched.group(3)}.watermarked.{matched.group(4)}'
        else:
            matched = re.search(
                '^comic-images/([a-zA-Z0-9\\-]+)/(\\d+)/img(\\d+)\\.(png|jpe?g|webp|avif)$',
                path)
            if matched:
                image = f'comics.{matched.group(1)}.{matched.group(2)}.{matched.group(3)}.{matched.group(4)}'
                new_name = f'img{matched.group(3)}.watermarked{p.suffix}'
    if image and new_name:
        if path.count('watermarked'):
            if (existing := pathlib.Path(path)).exists():
                existing.unlink()
            return
        async with sem:
            async with session.get(
                    f'http://localhost/gallery/img.php?img={image}&token={token}'
            ) as resp:
                new_path = p.with_name(new_name)
                with open(new_path, 'wb') as file:
                    file.write(await resp.read())
            pass
    pass


async def main():
    tasks = set()
    async with aiohttp.ClientSession() as session:
        async with asyncio.TaskGroup() as taskgrp:
            for path in [
                '404placeholder.png',
                *pathlib.Path('images').glob('*/*.*'),
                *pathlib.Path('images').glob('*/*.*.*'),
                *pathlib.Path('comic-images').glob('*/*/*.*'),
            ]:
                task = taskgrp.create_task(fetch(
                    str(path).replace('\\', '/'), session))
                task.add_done_callback(tasks.discard)
                tasks.add(task)
            pass
    pass


asyncio.run(main())
