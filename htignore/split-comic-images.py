import aiohttp
import asyncio
import pathlib
import re, json

sem = asyncio.Semaphore(75)
pass


async def fetch(name: str, episode: str, image: str, file_format: str, chunk: int, session: aiohttp.ClientSession,
                taskgrp, tasks):
    async with sem:
        async with session.get(
                f'http://localhost/gallery/comic-cropper.php?comic-name={name}&episode={episode}&imageId={image}&format={file_format}&chunk={chunk}'
        ) as resp:
            print(resp.status, name, episode, image, file_format)
            if resp.status != 200:
                print(json.dumps(dict(resp.headers), indent=2))
                print(await resp.text())
                print('---')
                return

            new_path = (pathlib.Path('./') / resp.headers['return_path']).resolve()
            with open(new_path, 'wb') as file:
                file.write(await resp.read())
            print('---')
            next_chunk = (chunk + 1) if resp.headers['chunk-next'] == '?1' else False
    if next_chunk:
        task = taskgrp.create_task(fetch(name, episode, image, file_format, next_chunk, session, taskgrp, tasks))
        task.add_done_callback(tasks.discard)
        tasks.add(task)
    pass


async def main():
    tasks = set()
    array = list(pathlib.Path('./comic-images').glob('*/*/raws/img[1234567890][1234567890][1234567890].*'))
    for unlinkable in pathlib.Path('./comic-images').glob('*/*/*img[1234567890][1234567890][1234567890]*.*'):
        unlinkable.unlink()
    print('---')
    async with aiohttp.ClientSession() as session:
        async with asyncio.TaskGroup() as taskgrp:
            for path in (p.resolve() for p in array):
                strx = str(path).replace('\\', '/')
                if bool(searched := re.search(
                        'comic-images/([a-zA-Z0-9]+)/(\\d+)/(?:raws/)?img(\\d{3})\\.(avif|png|jpe?g|webp)$',
                        strx)):
                    name, episode, image, file_format = searched.groups()
                    task = taskgrp.create_task(fetch(name, episode, image, file_format, int(), session, taskgrp, tasks))
                    task.add_done_callback(tasks.discard)
                    tasks.add(task)
            pass
    pass


if __name__ == '__main__':
    asyncio.run(main())

pass
