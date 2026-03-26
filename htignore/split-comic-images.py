import aiohttp
import asyncio
import pathlib
import re, os

sem = asyncio.Semaphore(75)
pass


async def fetch(name: str, episode: str, image: str, file_format: str, chunk: int, session: aiohttp.ClientSession,
                taskgrp, tasks):
    chunk_str = str(chunk).rjust(3, '0')
    async with sem:
        async with session.get(
                f'http://localhost/gallery/comic-cropper.php?comic-name={name}&episode={episode}&imageId={image}&format={file_format}&chunk={chunk_str}'
        ) as resp:
            # print(resp.status, name, episode, image, file_format)
            if resp.status != 200:
                # print(json.dumps(dict(resp.headers), indent=2))
                # print(await resp.text())
                # print('---')
                return

            new_path = (pathlib.Path('./') / resp.headers['return_path']).resolve()
            with open(new_path, 'wb') as file:
                file.write(await resp.read())
            # print('---')
            next_chunk = (chunk + 1) if resp.headers['chunk-next'] == '?1' else False
    if next_chunk:
        task = taskgrp.create_task(fetch(name, episode, image, file_format, next_chunk, session, taskgrp, tasks))
        task.add_done_callback(tasks.discard)
        tasks.add(task)
    pass


def reput_path_at(from_path: str | pathlib.Path, to_path: str | pathlib.Path):
    with open(from_path, 'rb') as src, open(to_path, 'wb') as out:
        return out.write(src.read())


def chatgpt(folder: pathlib.Path) -> None:
    """
    Renames images in the folder:
    - Removes '-img' prefix, keeps 'img'
    - Removes chunk part from filenames
    - Assigns sequential numbers to unique (main_index, chunk_index) groups
    - Preserves file extensions (multiple formats for same image share the same number)
    """
    pattern = re.compile(r"^-img(\d+)-(\d+)(\.[^.]+)$")

    files = list()

    # Parse files
    for file in folder.iterdir():
        if not file.is_file():
            continue
        match = pattern.match(file.name)
        if match:
            main_index = int(match.group(1))
            chunk_index = int(match.group(2))
            extension = match.group(3)
            files.append((main_index, chunk_index, file, extension))

    # Sort properly by main_index then chunk_index
    files.sort()

    # Assign new indices per unique (main_index, chunk_index)
    index_map = {}
    current_index = 1

    for main_index, chunk_index, _, _ in files:
        key = (main_index, chunk_index)
        if key not in index_map:
            index_map[key] = current_index
            current_index += 1

    padding = 3  # len(str(len(index_map)))

    # First rename to temporary names to avoid collisions
    temp_paths = []

    for main_index, chunk_index, file, extension in files:
        new_index = index_map[(main_index, chunk_index)]
        temp_name = f"__tmp__{new_index}{extension}"
        temp_path = folder / temp_name
        file.rename(temp_path)
        temp_paths.append((temp_path, new_index, extension))

    # Final rename
    for temp_path, new_index, extension in temp_paths:
        final_name = f"img{str(new_index).zfill(padding)}{extension}"
        final_path = folder / final_name
        temp_path.rename(final_path)
        # print(f"{temp_path.name} -> {final_name}")
    pass


async def main():
    tasks = set()
    array = list(pathlib.Path('./comic-images').glob('*/*/raws/img[1234567890][1234567890][1234567890].*'))
    for unlinkable in pathlib.Path('./comic-images').glob('*/*/*img[1234567890][1234567890][1234567890]*.*'):
        unlinkable.unlink()
    # print('---')
    async with aiohttp.ClientSession() as session:
        async with asyncio.TaskGroup() as taskgrp:
            for path in (p.resolve() for p in array):
                strx = str(path).replace('\\', '/')
                if bool(searched := re.search(
                        r'comic-images/([a-zA-Z0-9]+)/(\d+)/raws/img(\d{3})\.(avif|png|jpe?g|webp)$',
                        strx)):
                    name, episode, image, file_format = searched.groups()
                    task = taskgrp.create_task(fetch(name, episode, image, file_format, int(), session, taskgrp, tasks))
                    task.add_done_callback(tasks.discard)
                    tasks.add(task)
            pass
    for f in pathlib.Path('./comic-images').glob('*/*/'):
        chatgpt(f)
    # counter = 0
    # sameness = 0
    # for path in pathlib.Path('./comic-images').glob('*/*/-*.*'):
    #     strx = str(path).replace('\\', '/')
    #     if bool(searched := re.search(
    #             'comic-images/([a-zA-Z0-9]+)/(\\d+)/-img(\\d{3})-(\\d{3})\\.(avif|png|jpe?g|webp)$',
    #             strx)):
    #         name, episode, image, chunk, file_format = searched.groups()
    #         if int(image) == sameness:
    #             justed = str(counter).rjust(3, '0')
    #             reput_path_at(strx, f"comic-images/{name}/{episode}/img{justed}.{file_format}")
    #         else:
    #             justed = str(counter := counter + 1).rjust(3, '0')
    #             sameness = int(image)
    #             reput_path_at(strx, f"comic-images/{name}/{episode}/img{justed}.{file_format}")
    #     path.unlink()
    pass


if __name__ == '__main__':
    asyncio.run(main())

pass
