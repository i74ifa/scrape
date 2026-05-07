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
    Tooltip,
} from "@heroui/react";
import { MoreVertical, Plus, Search, Edit, Trash2, ToggleLeft, ToggleRight, Eye } from "lucide-react";
import { router } from "@inertiajs/react";

export default function Index({ tenants, filters }) {
    const handleSearch = (value) => {
        router.get(
            route("central.tenants.index"),
            { search: value, status: filters.status, plan: filters.plan },
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleStatusFilter = (value) => {
        router.get(
            route("central.tenants.index"),
            { search: filters.search, status: value, plan: filters.plan },
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleToggleStatus = (tenant) => {
        router.post(
            route("central.tenants.toggle-status", tenant.id),
            {},
            { preserveScroll: true }
        );
    };

    const handleDelete = (tenant) => {
        if (confirm(`هل أنت متأكد من حذف العميل "${tenant.name}"؟`)) {
            router.delete(route("central.tenants.destroy", tenant.id), {
                preserveScroll: true,
            });
        }
    };

    const statusColors = {
        true: "success",
        false: "default",
    };

    const statusLabels = {
        true: "نشط",
        false: "غير نشط",
    };

    return (
        <CentralAdminLayout title="العملاء">
            <div className="flex flex-col gap-8 pb-10">
                {/* Filters */}
                <div className="flex flex-col sm:flex-row gap-4 px-2">
                    <div className="flex-1">
                        <Input
                            placeholder="بحث بالاسم، النطاق، الهاتف..."
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
                        <option value="inactive">غير نشط</option>
                    </select>
                    <Button
                        color="primary"
                        onPress={() => router.get(route("central.tenants.create"))}
                    >
                        <Plus size={18} />
                        إضافة عميل
                    </Button>
                </div>

                <div className="px-2">
                    <h2 className="text-xl font-bold text-zinc-500 dark:text-zinc-400">
                        عرض {tenants.total || tenants.data.length} عميل
                    </h2>
                </div>

                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] overflow-hidden">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Tenants Table">
                                <TableHeader className="text-right">
                                    <TableColumn className="rtl:text-right" isRowHeader>
                                        الاسم
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        النطاق
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الحالة
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الخطة
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        تاريخ الاضافة
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الإجراءات
                                    </TableColumn>
                                </TableHeader>
                                <TableBody
                                    items={tenants.data}
                                    renderEmptyState={() => (
                                        <p className="text-center py-10 text-zinc-400">
                                            لم يتم العثور على عملاء
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
                                                    {item.name}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-500">
                                                    {item.domain?.domain || "—"}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <Chip
                                                    size="sm"
                                                    variant="flat"
                                                    color={statusColors[String(item.is_active)] || "default"}
                                                >
                                                    {statusLabels[String(item.is_active)] || (item.is_active ? "نشط" : "غير نشط")}
                                                </Chip>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-500">
                                                    {item.subscription?.plan_price?.plan?.name || "—"}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-400">
                                                    {item.created_at
                                                        ? new Date(item.created_at).toLocaleDateString("ar-SA")
                                                        : "—"}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Tooltip content="تعديل">
                                                        <Button
                                                            isIconOnly
                                                            size="sm"
                                                            variant="light"
                                                            onPress={() =>
                                                                router.get(
                                                                    route("central.tenants.edit", item.id)
                                                                )
                                                            }
                                                        >
                                                            <Edit size={16} />
                                                        </Button>
                                                    </Tooltip>
                                                    <Tooltip content="تغيير الحالة">
                                                        <Button
                                                            isIconOnly
                                                            size="sm"
                                                            variant="light"
                                                            onPress={() => handleToggleStatus(item)}
                                                        >
                                                            {item.is_active ? (
                                                                <ToggleLeft size={16} />
                                                            ) : (
                                                                <ToggleRight size={16} />
                                                            )}
                                                        </Button>
                                                    </Tooltip>
                                                    <Tooltip content="حذف">
                                                        <Button
                                                            isIconOnly
                                                            size="sm"
                                                            variant="light"
                                                            color="danger"
                                                            onPress={() => handleDelete(item)}
                                                        >
                                                            <Trash2 size={16} />
                                                        </Button>
                                                    </Tooltip>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </TableContent>
                        </Table>
                    </CardContent>
                </Card>

                {/* Pagination */}
                {tenants.links && tenants.links.length > 3 && (
                    <div className="flex justify-center gap-2">
                        {tenants.links.map((link, i) => (
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
