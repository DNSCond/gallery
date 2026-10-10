// preformatter
export function preformatter(string) {
    const pre = document.createElement('pre'),
        code = document.createElement('code');
    pre.append(code);
    code.textContent = string;
    return pre;
}
