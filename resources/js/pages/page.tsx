import Layout from "@/layout";
import axios from "axios";
import { useEffect, useState } from "react";
import { useParams } from "react-router";

export default function Page() {
    const { slug } = useParams();
    const [page, setPage] = useState(null);

    useEffect(() => {
        if (!slug) return;

        axios.get(`/api/pages/${slug}`).then((res) => {
            setPage(res.data);
        });
    }, [slug]);

    return (
        <Layout>
            <div className="max-w-5xl mx-auto px-10">
                <article
                    className="prose dark:prose-invert max-w-none"
                    dangerouslySetInnerHTML={{
                        __html: page?.content || "",
                    }}
                />
            </div>
        </Layout>
    );
}
