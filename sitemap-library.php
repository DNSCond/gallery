<?php use function Helpers\htmlspecialchars12;

$n = "\n    ";

enum changefreq: string
{
    case always = 'always';
    case hourly = 'hourly';
    case daily = 'daily';
    case weekly = 'weekly';
    case monthly = 'monthly';
    case yearly = 'yearly';
    case never = 'never';
}

enum HTMLPage
{
    case fullpage;
    case tableonly;
}

class XUrl implements JsonSerializable
{
    private null|string $changefreq = null;
    private int|null $lastMod = null;
    private readonly string $path;
    private int $priority = 5;
    private array $antproperties = array();
    private readonly string $baseURL;

    /**
     * @param string $path must have a leading slash
     */
    public function __construct(string $baseURL, string $path)
    {
        $this->baseURL = htmlspecialchars12($baseURL);
        $this->path = htmlspecialchars12($path);
    }

    public function set_changefreq(string|changefreq $changefreq): self
    {
        $this->changefreq = match ($changefreq) {
            changefreq::always, 'always' => 'always',
            changefreq::hourly, 'hourly' => 'hourly',
            changefreq::daily, 'daily' => 'daily',
            changefreq::weekly, 'weekly' => 'weekly',
            changefreq::monthly, 'monthly' => 'monthly',
            changefreq::yearly, 'yearly' => 'yearly',
            changefreq::never, 'never' => 'never',
            // default => throw
        };
        return $this;
    }

    public function set_lastMod(int $lastMod): self
    {
        $this->lastMod = $lastMod;
        return $this;
    }

    public function set_priority(int $priority): self
    {
        if ($priority < 0 || $priority > 10)
            throw new InvalidArgumentException();
        $this->priority = $priority;
        return $this;
    }

    public function setANTProperty(string $name, string|int|null $value): bool
    {
        if ($value === null) {
            unset($this->antproperties[$name]);
        } else {
            if (preg_match('/^[a-z][a-z0-9\\-]+$/iD', $name)) {
                $this->antproperties[$name] = htmlspecialchars12($value);
            } else return false;
        }
        return true;
    }

    public function __toString(): string
    {
        global $n;
        $attributes = '';
        foreach ($this->antproperties as $antproperty => $antvalue) {
            $attributes .= " ant:$antproperty=\"$antvalue\"";
        }
        $result = "\n<url$attributes><loc>$this->baseURL$this->path</loc>";
        if (is_int($this->lastMod)) {
            $lastMod = gmdate('Y-m-d', $this->lastMod);
            $antLM = gmdate('D, M, Y-m-d H:i:s \\U\\T\\C', $this->lastMod);
            $result .= "$n<lastmod ant:Last-Modified=\"$antLM\">$lastMod</lastmod>";
        }
        if ($this->changefreq) {
            $result .= "$n<changefreq>$this->changefreq</changefreq>";
        }
        if ($this->priority === 10) {
            $result .= "$n<priority>1.0</priority>";
        } else {
            $result .= "$n<priority>0.$this->priority</priority>";
        }
        return "$result\n</url>";
    }

    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    public function toString(): string
    {
        return "$this";
    }

    public function jsonSerialize(): array
    {
        return array(
            'loc' => "$this->baseURL$this->path",
            'lastmod' => is_int($this->lastMod) ? gmdate('Y-m-d\\TH:i:s\\Z', $this->lastMod) : null,
            'priority' => +($this->priority === 10 ? '1.0' : "0.$this->priority"), "changefreq" => $this->changefreq,
        );
    }

    public function asHTML_TR(): string
    {
        if (is_int($this->lastMod)) {
            $lastMod = gmdate('Y-m-d\\TH:i:s\\Z', $this->lastMod);
            $antLM = gmdate('D, M, Y-m-d H:i:s \\U\\T\\C', $this->lastMod);
            $lastMod = "<time datetime=$lastMod>$antLM</time>";
        } else {
            $lastMod = 'null';
        }
        $priority = +($this->priority === 10 ? '1.0' : "0.$this->priority");
        //$changefreq = ($this->changefreq ?: 'null');
        // return "<tr><td><a href=$this->path>$this->path</a><td>$changefreq<td>$lastMod";
        return "<tr><td><a href=\"$this->path\">$this->path</a><td>$lastMod<td>$priority";
    }
}

class XUrlSet implements JsonSerializable
{
    private XUrl $main;
    private array $urls;
    private bool $withOuter = false;
    private readonly string $baseURL;

    public function __construct(XUrl $main)
    {
        $this->baseURL = ($this->main = $main)->getBaseURL();
        $this->urls = [$this->main];
    }

    /**
     * @param string $path must have a leading slash
     */
    public function addXUrl(string $path): XUrl
    {
        $new = new XUrl($this->baseURL, $path);
        return $this->urls[] = $new;
    }

    public function set_withOuter(bool $withOuter): self
    {
        $this->withOuter = $withOuter;
        return $this;
    }

    public function __toString(): string
    {
        global $n;
        $entries = count($this->urls);
        $xUrls = implode($n, $this->urls);
        $antURL = 'https://antrequest.nl/gallery/sitemap.php';
        $sitemapURL = 'https://www.sitemaps.org/schemas/sitemap/0.9';
        return !$this->withOuter ? $xUrls :
            "<urlset ant:entries=\"{$entries}n\" xmlns=\"$sitemapURL\" xmlns:ant=\"$antURL\">\n$xUrls</urlset>";
    }

    public function toString(): string
    {
        return "$this";
    }

    public function jsonSerialize(): array
    {
        return ['urlset' => $this->urls];
    }

    public function asHTML(HTMLPage $page, ?string $domainName = null): string
    {
        $result = '';
        foreach ($this->urls as $url) {
            $result .= "{$url->asHTML_TR()}\n";
        }
        // $thead = "<thead><tr><th scope=col>Path<th scope=col>changefreq<th scope=col>LastMod</thead>";
        $thead = "<thead><tr><th scope=col>Path<th scope=col>LastMod<th scope=col>priority</thead>";
        $table = "<table>$thead<tbody>\n$result</tbody></table>";
        if ($page === HTMLPage::tableonly) {
            return $table;
        } else {
            $script = 'document.querySelectorAll(\'time\').forEach(each=>each.textContent=new Date(each.dateTime).toString().slice(0,33));';
            $title = '';
            if ($domainName) {
                $domainName = htmlspecialchars12($domainName);
                $h1 = $domainName ? "<h1><a href=\"https://$domainName\">$domainName</a> Sitemap</h1>\n" : '';
                $title = "<title>$domainName Sitemap</title>";
            } else {
                $h1 = '';
            }
            return "<!DOCTYPE html><meta charset=UTF-8><base href=$this->baseURL>$title\n<style>html{font-family:"
                . "monospace;color:black;background-color: white;}a:visited,a:link{color:blue;}a:hover{color:orangered"
                . ";}a:active{color: black;}@media(prefers-color-scheme:dark){a:visited,a:link{color:#94DDFF;}a:hover" .
                "{color:orangered;}a:active{color:white;}html{color: #ffffff;background-color: #171717;}}" .
                "table{width:100%;border-collapse:collapse;}th,td{border:3px solid #7c7c7c;text-align:left;"
                . "padding:8px;box-sizing:border-box;height:100%;}table{width:fit-content;}td:nth-child(3)" .
                '>span{background-color:white;padding:0.3em;color:black;}</style><meta name=viewport content' .
                "=\"width=device-width, initial-scale=1\">\n$h1$table<script>$script</script>" .
                "<div style=height:32vh></div>\n";
        }
    }
}
