from bs4 import BeautifulSoup, NavigableString, Comment


def html_to_jsonml(html_str):
    """
    Converts an HTML string into JsonML array structure using BeautifulSoup.
    """
    soup = BeautifulSoup(html_str, 'html.parser')

    def parse_node(node):
        # Handle string/text nodes
        if isinstance(node, NavigableString):
            if isinstance(node, Comment):
                return None  # Ignore HTML comments
            return str(node)

        # Base JsonML element: [tag_name]
        element = [node.name]

        # Extract attributes
        attrs = {}
        for key, value in node.attrs.items():
            # BeautifulSoup returns 'class' as a list; join back into space-separated string
            if isinstance(value, list):
                attrs[key] = " ".join(value)
            else:
                attrs[key] = value

        # If attributes exist, append as second element
        if attrs:
            element.append(attrs)
        else:
            element.append(dict())

        # Recursively process children
        for child in node.children:
            child_jsonml = parse_node(child)
            if child_jsonml is not None:
                # Omit empty whitespace-only strings between block elements if desired
                if isinstance(child_jsonml, str) and not child_jsonml.strip():
                    continue
                element.append(child_jsonml)

        return element

    # Parse body/fragment or first non-document element
    top_element = soup.body if soup.body else soup
    children = [parse_node(c) for c in top_element.children if parse_node(c) is not None]

    # Return single root element if present, otherwise wrapper array
    if len(children) == 1 and isinstance(children[0], list):
        return children[0]
    return ["div", *children]  # children


pass
