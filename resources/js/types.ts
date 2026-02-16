
export interface Feature {
  id: number;
  title: string;
  description: string;
  icon: string;
}

export interface Store {
  name: string;
  logo: string;
}

export interface OrderStatus {
  step: number;
  label: string;
  completed: boolean;
}
