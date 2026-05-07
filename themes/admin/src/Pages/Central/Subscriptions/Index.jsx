import CentralAdminLayout from "../../../Layouts/CentralAdminLayout";
import {
    Table,
    TableContent,
    TableHeader,
    TableColumn,
    TableBody,
    TableRow,
    TableCell,
    Chip,
    Card,
    CardContent,
    Button,
    Input,
} from "@heroui/react";
import { Search, Edit, CalendarDays } from "lucide-react";
import { router } from "@inertiajs/react";

export default function Index({ subscriptions, filters }) {
    const handleSearch = (value) => {
        router.get(
            route("central.subscriptions.index"),
            { search: value, status: filters.status },
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleStatusFilter = (value) => {
        router.get(
            route("central.subscriptions.index"),
            { search: filters.search, status: value },
            { preserveState: true, preserveScroll: true }
        );
    };

    const statusColors = {
        active: "success",
        expired: "danger",
        cancelled: "warning",
        trial: "primary",
    };

    const statusLabels = {
        active: "نشط",
        expired: "منتهي",
        cancelled: "ملغي",
        trial: "تجريبي",
    };

    return (
        <CentralAdminLayout title="الاشتراكات">
            <div className="flex flex-col gap-8 pb-10">
                {/* Filters */}
                <div className="flex flex-col sm:flex-row gap-4 px-2">
                    <div className="flex-1">
                        <Input
                            placeholder="بحث باسم العميل..."
                            defaultValue={filters.search || ""}
                            startContent={<Search size={18} />}
                            className="w-full"
                            onValueChange={handleSearch}
                        />
                    </div>
                    <select
                        className="h-12 px-4 rounded-xl bg-zinc-200/50 dark:bg-zinc-800/50 text-sm border-none outline-none text-zinc-700 dark:text-zinc-300"
                        value={filters.status || ""}
                        onChange={(e) => handleStatusFilter(e.target.value)}
                    >
                        <option value="">تصفية بالحالة</option>
                        <option value="active">نشط</option>
                        <option value="expired">منتهي</option>
                        <option value="cancelled">ملغي</option>
                        <option value="trial">تجريبي</option>
                    </select>
                </div>

                <div className="px-2">
                    <h2 className="text-xl font-bold text-zinc-500 dark:text-zinc-400">
                        عرض {subscriptions.total || subscriptions.data.length} اشتراك
                    </h2>
                </div>

                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] overflow-hidden">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Subscriptions Table">
                                <TableHeader className="text-right">
                                    <TableColumn className="rtl:text-right" isRowHeader>
                                        العميل
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الخطة
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الحالة
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        المبلغ
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        بداية الفترة
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        نهاية الفترة
                                    </TableColumn>
                                </TableHeader>
                                <TableBody
                                    items={subscriptions.data}
                                    renderEmptyState={() => (
                                        <p className="text-center py-10 text-zinc-400">
                                            لم يتم العثور على اشتراكات
                                        </p>
                                    )}
                                >
                                    {(item) => (
                                        <TableRow
                                            id={item.id}
                                            className="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                        >
                                            <TableCell>
                                                <p className="font-semibold text-sm">
                                                    {item.tenant?.name || "—"}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <Chip size="sm" variant="flat" color="primary">
                                                    {item.plan_price?.plan?.name || "—"}
                                                </Chip>
                                            </TableCell>
                                            <TableCell>
                                                <Chip
                                                    size="sm"
                                                    variant="flat"
                                                    color={statusColors[item.status] || "default"}
                                                >
                                                    {statusLabels[item.status] || item.status}
                                                </Chip>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-500">
                                                    {item.amount_paid ? `${item.amount_paid} د.ع` : "—"}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-400">
                                                    {item.current_period_start
                                                        ? new Date(item.current_period_start).toLocaleDateString("ar-SA")
                                                        : "—"}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-400">
                                                    {item.current_period_end
                                                        ? new Date(item.current_period_end).toLocaleDateString("ar-SA")
                                                        : "—"}
                                                </p>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </TableContent>
                        </Table>
                    </CardContent>
                </Card>

                {/* Pagination */}
                {subscriptions.links && subscriptions.links.length > 3 && (
                    <div className="flex justify-center gap-2">
                        {subscriptions.links.map((link, i) => (
                            <Button
                                key={i}
                                size="sm"
                                color={link.active ? "primary" : "default"}
                                variant={link.active ? "solid" : "flat"}
                                isDisabled={!link.url}
                                onPress={() => router.get(link.url, {}, { preserveState: true })}
                            >
                                {link.label.replace("&laquo;", "«").replace("&raquo;", "»")}
                            </Button>
                        ))}
                    </div>
                )}
            </div>
        </CentralAdminLayout>
    );
}
