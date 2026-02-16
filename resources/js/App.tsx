import React, { useState, useEffect } from "react";
import { Card } from "./components/Card";
import { Button } from "./components/Button";

// SVG Logo Component
const Logo = () => (
  <div className="flex items-center gap-3 group cursor-pointer">
    <div className="relative w-10 h-10 md:w-12 md:h-12">
      <img
        src="/3d-logo-compressed.webp"
        className="w-full h-full"
        alt="logo"
      />
      <div className="absolute inset-0"></div>
    </div>
  </div>
);

// Icon Components for Timeline
const TimelineIcons = {
  received: () => (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      className="h-5 w-5"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
      />
    </svg>
  ),
  processing: () => (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      className="h-5 w-5"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
      />
    </svg>
  ),
  shipped: () => (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      className="h-5 w-5"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
      />
    </svg>
  ),
  arrival: () => (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      className="h-5 w-5"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
      />
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
      />
    </svg>
  ),
  delivery: () => (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      className="h-5 w-5"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth={2}
        d="M5 13l4 4L19 7"
      />
    </svg>
  ),
};

const getMockTrackingData = (id: string) => ({
  id: id || "TAL-000000",
  status: "في الطريق",
  statusAr: "جاري التوصيل",
  percentage: 65,
  origin: "مستودع أمازون - دبي، الإمارات",
  destination: "الرياض، المملكة العربية السعودية",
  estimatedDelivery: "25 مايو 2024",
  timeline: [
    {
      time: "21 مايو، 10:30 ص",
      title: "تم استلام الطلب",
      completed: true,
      type: "received",
    },
    {
      time: "21 مايو، 02:15 م",
      title: "تم التجهيز في المستودع",
      completed: true,
      type: "processing",
    },
    {
      time: "22 مايو، 08:00 ص",
      title: "غادر مركز التوزيع الدولي",
      completed: true,
      type: "shipped",
    },
    {
      time: "23 مايو، 11:45 م",
      title: "وصل إلى مركز الفرز بالرياض",
      completed: false,
      type: "arrival",
    },
    {
      time: "متوقع غداً",
      title: "خارج للتوصيل",
      completed: false,
      type: "delivery",
    },
  ],
});

const App: React.FC = () => {
  const [view, setView] = useState<"home" | "tracking">("home");
  const [trackingNumber, setTrackingNumber] = useState("");
  const [activeTrackingId, setActiveTrackingId] = useState<string | null>(null);
  const [isSearching, setIsSearching] = useState(false);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("trackingId");
    if (id) {
      setActiveTrackingId(id);
      setView("tracking");
    }

    const handlePopState = () => {
      const p = new URLSearchParams(window.location.search);
      const trackingId = p.get("trackingId");
      if (trackingId) {
        setActiveTrackingId(trackingId);
        setView("tracking");
      } else {
        setView("home");
        setActiveTrackingId(null);
      }
    };

    window.addEventListener("popstate", handlePopState);
    return () => window.removeEventListener("popstate", handlePopState);
  }, []);

  const handleTrackAction = (id: string) => {
    if (!id.trim()) return;
    setIsSearching(true);

    setTimeout(() => {
      setIsSearching(false);
      setActiveTrackingId(id);
      setView("tracking");

      try {
        const newSearch = `?trackingId=${encodeURIComponent(id)}`;
        window.history.pushState({ trackingId: id }, "", newSearch);
      } catch (e) {
        console.warn("History pushState failed:", e);
      }
    }, 800);
  };

  const goHome = () => {
    setView("home");
    setActiveTrackingId(null);
    setTrackingNumber("");
    try {
      window.history.pushState({}, "", window.location.pathname);
    } catch (e) {
      console.warn("History pushState failed:", e);
    }
  };

  const AppStoreIcon = () => (
    <svg className="w-6 h-6 ml-2" viewBox="0 0 24 24" fill="currentColor">
      <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
    </svg>
  );

  const PlayStoreIcon = () => (
    <svg className="w-6 h-6 ml-2" viewBox="0 0 24 24" fill="currentColor">
      <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.61 3,21.09 3,20.5M16.81,15.12L18.81,16.12L4.54,23.33L15.08,12.79L16.81,15.12M15.08,11.21L4.54,0.67L18.81,7.88L16.81,8.88L15.08,11.21M16.03,12.11L18.89,9.25L21.03,10.32C21.67,10.64 22,11.3 22,12C22,12.7 21.67,13.36 21.03,13.68L18.89,14.75L16.03,12.11Z" />
    </svg>
  );

  const renderHome = () => (
    <>
      <section className="container mx-auto px-6 py-12 md:py-24 grid md:grid-cols-2 gap-12 items-center relative z-10">
        <div className="text-right space-y-8 animate-fade-in">
          <h1 className="text-4xl md:text-6xl font-bold leading-tight">
            كل طلباتك في <span className="text-blue-500">مكان واحد</span>
          </h1>
          <p className="text-xl text-gray-400 max-w-lg leading-relaxed">
            تسوق من أمازون، إيباي، شي إن وغيرهم الكثير. أضف للسلة، تتبع الشحنات،
            واستلمها بسرعة البرق عبر تطبيقنا.
          </p>
          <div className="flex flex-col sm:flex-row gap-4">
            <Button className="flex items-center justify-center text-sm md:text-base px-6">
              <AppStoreIcon />
              <span>تحميل من App Store</span>
            </Button>
            <Button
              variant="secondary"
              className="flex items-center justify-center text-sm md:text-base px-6"
            >
              <PlayStoreIcon />
              <span>تحميل من Google Play</span>
            </Button>
          </div>
        </div>

        <div
          className="relative animate-fade-in"
          style={{ animationDelay: "0.2s" }}
        >
          <Card className="relative z-10 border border-[#333333] shadow-2xl">
            <div className="space-y-6">
              <div className="flex justify-between items-center mb-4">
                <span className="bg-green-500/20 text-green-500 px-4 py-1 rounded-full text-xs md:text-sm font-bold">
                  نشط في التطبيق
                </span>
                <span className="text-gray-500 text-xs md:text-sm font-medium">
                  #TAL-882941
                </span>
              </div>
              <div className="h-2 bg-[#333333] rounded-full overflow-hidden">
                <div className="w-2/3 h-full bg-blue-500"></div>
              </div>
              <div className="flex justify-between text-xs text-gray-400">
                <span>تم الشحن</span>
                <span>في الطريق</span>
                <span>تم الاستلام</span>
              </div>
              <div className="pt-4 border-t border-[#333333] flex items-center gap-4 text-right">
                <div className="w-12 h-12 bg-[#333333] rounded-2xl flex items-center justify-center overflow-hidden">
                  <img
                    src="https://picsum.photos/seed/amazon/50/50"
                    alt="Store"
                    className="opacity-50"
                  />
                </div>
                <div>
                  <div className="font-bold">أمازون - سماعات سوني</div>
                  <div className="text-xs text-gray-500 font-mono">
                    تحديث: منذ 10 دقائق
                  </div>
                </div>
              </div>
            </div>
          </Card>
          <div className="absolute -bottom-6 -left-6 w-full h-full bg-blue-600/10 rounded-[2.5rem] -z-0"></div>
        </div>
      </section>

      <section className="container mx-auto px-6 py-12 relative z-10">
        <Card className="max-w-4xl mx-auto text-center border border-[#333333] bg-[#262626]/80 backdrop-blur-md">
          <h2 className="text-3xl font-bold mb-8">تتبع شحنتك فوراً</h2>
          <form
            onSubmit={(e) => {
              e.preventDefault();
              handleTrackAction(trackingNumber);
            }}
            className="flex flex-col md:flex-row gap-4"
          >
            <input
              type="text"
              placeholder="أدخل رقم التتبع هنا..."
              value={trackingNumber}
              onChange={(e) => setTrackingNumber(e.target.value)}
              className="flex-1 bg-[#1A1A1A]/80 border-2 border-[#333333] rounded-full px-6 py-4 text-white focus:border-blue-500 outline-none transition-colors"
            />
            <Button type="submit" className="md:w-48 h-[60px]">
              {isSearching ? (
                <div className="flex items-center justify-center gap-2">
                  <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                  <span>جاري البحث...</span>
                </div>
              ) : (
                "تتبع الآن"
              )}
            </Button>
          </form>
        </Card>
      </section>

      <section className="container mx-auto px-6 py-24 relative z-10">
        <div className="text-center mb-16">
          <h2 className="text-4xl font-bold mb-4">لماذا تطبيق "طلبي"؟</h2>
          <p className="text-gray-400">
            نحن نقدم تجربة تسوق عالمية بمعايير محلية من خلال واجهة سهلة
          </p>
        </div>
        <div className="grid md:grid-cols-3 gap-8 text-right">
          <Card className="hover:bg-[#2d2d2d] transition-all duration-300 border border-transparent hover:border-[#444444] bg-[#262626]/60 backdrop-blur-sm">
            <div className="w-16 h-16 bg-blue-600/20 text-blue-500 rounded-3xl flex items-center justify-center mb-6">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-8 w-8"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M13 10V3L4 14h7v7l9-11h-7z"
                />
              </svg>
            </div>
            <h3 className="text-2xl font-bold mb-4">شحن فائق السرعة</h3>
            <p className="text-gray-400 leading-relaxed">
              نضمن وصول شحناتك من أي مكان في العالم إلى باب منزلك بسرعة البرق.
            </p>
          </Card>
          <Card className="hover:bg-[#2d2d2d] transition-all duration-300 border border-transparent hover:border-[#444444] bg-[#262626]/60 backdrop-blur-sm">
            <div className="w-16 h-16 bg-purple-600/20 text-purple-500 rounded-3xl flex items-center justify-center mb-6">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-8 w-8"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
            </div>
            <h3 className="text-2xl font-bold mb-4">سلة تسوق موحدة</h3>
            <p className="text-gray-400 leading-relaxed">
              اجمع منتجاتك من أمازون، إيباي، وشي إن في سلة واحدة داخل التطبيق.
            </p>
          </Card>
          <Card className="hover:bg-[#2d2d2d] transition-all duration-300 border border-transparent hover:border-[#444444] bg-[#262626]/60 backdrop-blur-sm">
            <div className="w-16 h-16 bg-green-600/20 text-green-500 rounded-3xl flex items-center justify-center mb-6">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-8 w-8"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                />
              </svg>
            </div>
            <h3 className="text-2xl font-bold mb-4">حماية وأمان تام</h3>
            <p className="text-gray-400 leading-relaxed">
              تأمين شامل على جميع الشحنات. فريقنا يتابع كل قطعة لضمان وصولها
              سليمة.
            </p>
          </Card>
        </div>
      </section>

      <section className="bg-[#262626]/40 backdrop-blur-lg py-24 relative z-10">
        <div className="container mx-auto px-6 text-center">
          <h2 className="text-3xl font-bold mb-16">
            تصفح المتاجر العالمية واطلبها في "طلبي"
          </h2>
          <div className="flex flex-wrap justify-center items-center gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
            <div className="text-3xl font-bold">Amazon</div>
            <div className="text-3xl font-bold">eBay</div>
            <div className="text-3xl font-bold">SHEIN</div>
            <div className="text-3xl font-bold">AliExpress</div>
            <div className="text-3xl font-bold">Walmart</div>
            <div className="text-3xl font-bold">iHerb</div>
          </div>
        </div>
      </section>

      <section className="container mx-auto px-6 py-24 relative z-10">
        <Card className="bg-blue-600 text-center text-white overflow-hidden relative border-none shadow-2xl">
          <div className="relative z-10 py-8">
            <h2 className="text-4xl md:text-5xl font-bold mb-6">
              احمل التطبيق وابدأ التسوق الآن
            </h2>
            <p className="text-blue-100 text-xl mb-10 max-w-2xl mx-auto">
              انضم إلى آلاف المتسوقين الذين يثقون في "طلبي" لإدارة مشترياتهم.
            </p>
            <div className="flex flex-col sm:flex-row justify-center gap-4">
              <button className="bg-white text-blue-600 px-8 py-4 rounded-full font-bold text-lg md:text-xl hover:bg-gray-100 transition-colors flex items-center justify-center">
                <AppStoreIcon /> App Store
              </button>
              <button className="bg-blue-800 text-white px-8 py-4 rounded-full font-bold text-lg md:text-xl hover:bg-blue-900 transition-colors flex items-center justify-center">
                <PlayStoreIcon /> Google Play
              </button>
            </div>
          </div>
          <div className="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div className="absolute bottom-0 left-0 w-32 h-32 bg-black/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        </Card>
      </section>
    </>
  );

  const renderTracking = () => {
    const data = getMockTrackingData(activeTrackingId || "---");
    return (
      <div className="container mx-auto px-6 py-12 animate-fade-in relative z-10">
        <div className="flex items-center justify-between mb-12">
          <Button
            variant="outline"
            onClick={goHome}
            className="flex items-center gap-2"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            <span>العودة للرئيسية</span>
          </Button>
          <div className="text-right">
            <h1 className="text-2xl md:text-4xl font-bold">تفاصيل الشحنة</h1>
            <p className="text-gray-500 font-mono mt-1">
              رقم التتبع: {data.id}
            </p>
          </div>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2 space-y-8">
            <Card className="border border-[#333333] bg-[#262626]/80 backdrop-blur-md">
              <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
                <div className="flex items-center gap-4">
                  <div className="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-900/20">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-8 w-8 text-white"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
                      />
                    </svg>
                  </div>
                  <div className="text-right">
                    <h2 className="text-2xl font-bold">{data.statusAr}</h2>
                    <p className="text-gray-400">
                      تاريخ الوصول المتوقع:{" "}
                      <span className="text-white font-bold">
                        {data.estimatedDelivery}
                      </span>
                    </p>
                  </div>
                </div>
                <div className="flex flex-col items-end">
                  <span className="bg-blue-600/20 text-blue-400 px-6 py-2 rounded-full font-bold text-lg">
                    {data.percentage}%
                  </span>
                  <span className="text-[10px] text-gray-500 mt-2 tracking-widest uppercase text-right">
                    جاري التتبع
                  </span>
                </div>
              </div>
              <div className="relative h-4 bg-[#1A1A1A] rounded-full overflow-hidden mb-12">
                <div
                  className="h-full bg-blue-500 rounded-full transition-all duration-1000 shadow-[0_0_20px_rgba(59,130,246,0.3)]"
                  style={{ width: `${data.percentage}%` }}
                ></div>
              </div>
              <div className="grid md:grid-cols-2 gap-8 border-t border-[#333333] pt-8 text-right">
                <div>
                  <label className="text-gray-500 text-sm block mb-1">من</label>
                  <p className="font-bold text-lg">{data.origin}</p>
                </div>
                <div>
                  <label className="text-gray-500 text-sm block mb-1">
                    إلى
                  </label>
                  <p className="font-bold text-lg">{data.destination}</p>
                </div>
              </div>
            </Card>

            <Card className="border border-[#333333] bg-[#262626]/80 backdrop-blur-md">
              <h3 className="text-xl font-bold mb-8 text-right">سجل التتبع</h3>
              <div className="space-y-0 relative before:absolute before:right-[21px] before:top-4 before:bottom-4 before:w-[2px] before:bg-[#333333]">
                {data.timeline.map((item, idx) => {
                  const IconComponent =
                    TimelineIcons[item.type as keyof typeof TimelineIcons] ||
                    (() => null);
                  const isLastCompleted =
                    item.completed &&
                    (!data.timeline[idx + 1] ||
                      !data.timeline[idx + 1].completed);

                  return (
                    <div
                      key={idx}
                      className={`relative pr-16 pb-12 last:pb-0 text-right transition-opacity duration-300 ${item.completed ? "opacity-60" : "opacity-100"}`}
                    >
                      <div
                        className={`absolute right-0 top-1 w-11 h-11 rounded-2xl border-4 border-[#262626] z-10 flex items-center justify-center transition-all duration-500 ${
                          item.completed
                            ? "bg-[#333333] text-gray-500"
                            : isLastCompleted ||
                                (!item.completed &&
                                  data.timeline[idx - 1]?.completed)
                              ? "bg-blue-600 text-white shadow-lg shadow-blue-900/40 scale-110"
                              : "bg-[#1A1A1A] text-gray-700 border-[#333333]"
                        }`}
                      >
                        <IconComponent />
                      </div>

                      <div className="group">
                        <div
                          className={`mb-1 font-bold text-lg transition-colors duration-300 ${
                            !item.completed && data.timeline[idx - 1]?.completed
                              ? "text-blue-400"
                              : ""
                          }`}
                        >
                          {item.title}
                        </div>
                        <div className="text-gray-500 text-sm">{item.time}</div>
                      </div>

                      {!item.completed && data.timeline[idx - 1]?.completed && (
                        <div className="absolute left-0 top-2 flex items-center gap-2">
                          <span className="relative flex h-3 w-3">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span className="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                          </span>
                          <span className="text-blue-400 text-xs font-bold uppercase tracking-wider">
                            الحالة الحالية
                          </span>
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </Card>
          </div>

          <div className="space-y-8 text-right">
            <Card className="bg-blue-600 text-white border-none overflow-hidden relative group shadow-xl">
              <div className="relative z-10">
                <h3 className="text-xl font-bold mb-4">هل تحتاج لمساعدة؟</h3>
                <p className="text-blue-100 mb-6">
                  فريق الدعم الفني متواجد لمساعدتك في أي استفسار حول شحنتك.
                </p>
                <Button
                  variant="secondary"
                  className="w-full bg-white text-blue-600 hover:bg-gray-100 border-none transition-transform group-hover:scale-[1.02]"
                >
                  تحدث مع الدعم
                </Button>
              </div>
              <div className="absolute -bottom-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            </Card>

            <Card className="border border-[#333333] hover:border-[#444444] transition-colors bg-[#262626]/80 backdrop-blur-md shadow-lg">
              <h3 className="text-lg font-bold mb-6 flex items-center justify-end gap-2">
                <span>بيانات الطلب</span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="h-5 w-5 text-gray-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </h3>
              <div className="space-y-4">
                <div className="flex justify-between items-center text-sm p-3 bg-[#1A1A1A]/60 rounded-2xl">
                  <span className="font-bold">Amazon.sa</span>
                  <span className="text-gray-500">المتجر</span>
                </div>
                <div className="flex justify-between items-center text-sm p-3 bg-[#1A1A1A]/60 rounded-2xl">
                  <span className="font-bold">1.2 كجم</span>
                  <span className="text-gray-500">الوزن</span>
                </div>
                <div className="flex justify-between items-center text-sm p-3 bg-[#1A1A1A]/60 rounded-2xl">
                  <span className="font-bold">2</span>
                  <span className="text-gray-500">عدد القطع</span>
                </div>
              </div>
            </Card>
          </div>
        </div>
      </div>
    );
  };

  return (
    <div className="min-h-screen bg-[#1A1A1A] text-white overflow-x-hidden relative">
      <nav className="container mx-auto px-6 py-8 flex items-center justify-between relative z-50">
        <div onClick={goHome}>
          <Logo />
        </div>
        <div className="hidden md:flex gap-8 items-center">
          <a
            href="#"
            className="hover:text-blue-400 transition-colors font-medium"
            onClick={(e) => {
              e.preventDefault();
              goHome();
            }}
          >
            الرئيسية
          </a>
          <a
            href="#"
            className="hover:text-blue-400 transition-colors font-medium"
          >
            خدماتنا
          </a>
          <a
            href="#"
            className="hover:text-blue-400 transition-colors font-medium"
          >
            المتاجر المدعومة
          </a>
          <Button variant="outline">تسجيل الدخول</Button>
        </div>
        <div className="md:hidden">
          <button className="text-white p-2">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-8 w-8"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M4 6h16M4 12h16m-7 6h7"
              />
            </svg>
          </button>
        </div>
      </nav>

      <main className="relative z-10">
        {view === "home" ? renderHome() : renderTracking()}
      </main>

      <footer className="container mx-auto px-6 py-12 border-t border-[#333333] mt-24 relative z-10">
        <div className="grid md:grid-cols-4 gap-12 text-right">
          <div className="space-y-4">
            <div onClick={goHome}>
              <Logo />
            </div>
            <p className="text-gray-500">
              بوابتك للتسوق العالمي بلا حدود وبأقصى سرعة ممكنة عبر تطبيقنا
              الذكي.
            </p>
          </div>
          <div className="space-y-4">
            <h4 className="font-bold text-lg">الروابط</h4>
            <ul className="space-y-2 text-gray-500">
              <li>
                <a
                  href="#"
                  className="hover:text-blue-400"
                  onClick={(e) => {
                    e.preventDefault();
                    goHome();
                  }}
                >
                  الرئيسية
                </a>
              </li>
              <li>
                <a href="#" className="hover:text-blue-400">
                  تحميل التطبيق
                </a>
              </li>
              <li>
                <a href="#" className="hover:text-blue-400">
                  الأسئلة الشائعة
                </a>
              </li>
            </ul>
          </div>
          <div className="space-y-4">
            <h4 className="font-bold text-lg">القانونية</h4>
            <ul className="space-y-2 text-gray-500">
              <li>
                <a href="#" className="hover:text-blue-400">
                  سياسة الخصوصية
                </a>
              </li>
              <li>
                <a href="#" className="hover:text-blue-400">
                  الشروط والأحكام
                </a>
              </li>
            </ul>
          </div>
          <div className="space-y-4">
            <h4 className="font-bold text-lg">تابعنا</h4>
            <div className="flex justify-end gap-4">
              <div className="w-10 h-10 bg-[#333333] rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors cursor-pointer">
                T
              </div>
              <div className="w-10 h-10 bg-[#333333] rounded-full flex items-center justify-center hover:bg-blue-400 transition-colors cursor-pointer">
                X
              </div>
              <div className="w-10 h-10 bg-[#333333] rounded-full flex items-center justify-center hover:bg-red-600 transition-colors cursor-pointer">
                I
              </div>
            </div>
          </div>
        </div>
        <div className="mt-12 pt-8 border-t border-[#333333] text-center text-gray-600 text-sm">
          جميع الحقوق محفوظة &copy; {new Date().getFullYear()} لمنصة طلبي
        </div>
      </footer>
    </div>
  );
};

export default App;
